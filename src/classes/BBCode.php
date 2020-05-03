<?php namespace BBCode; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('HTML.php');
require_once ('Localization.php');

class Parser
{
  function __construct ($language)
  {
    $this->language = $language;
  }

  function start ($root, $text)
  {
    $this->html_elements = [$root];
    $this->bbcode_elements = [];
    $this->text = $text;
    $this->remaining = strlen ($text);

    $this->state_normal();
  }

  private $html_elements;
  private $bbcode_elements;
  private $language;

  private $text;
  private $pos = 0;
  private $remaining;

  private $consumed = '';

  function consume ($count = 1)
  {
    $what = '';
    while ($count && $this->remaining)
    {
      $what .= $this->peek();
      $this->pos++;
      $this->remaining--;
      $count--;
    }
    $this->consumed .= $what;
    return $count === 0 ? $what : NULL;
  }
  function peek ($count = 1)
  {
    if ($this->remaining < $count)
    {
      return FALSE;
    }
    return substr ($this->text, $this->pos, $count);
  }
  function flush_consumed()
  {
    HTML_append_text ($this->current_html_element(), $this->consumed);
    $this->discard_consumed();
  }
  function discard_consumed()
  {
    $this->consumed = '';
  }
  function consume_while ($continuepred)
  {
    $state = '';
    while ($this->remaining && $continuepred ($this->peek()))
    {
      $state .= $this->consume();
    }
    return $state;
  }

  function consume_until ($needle)
  {
    return $this->consume_while (function ($char) use($needle) { return $char !== $needle; });
  }
  function consume_if ($next)
  {
    $len = strlen ($next);
    return $this->peek ($len) === $next && $this->consume ($len) === $next;
  }

  function current_html_element()
  {
    $count = count ($this->html_elements);
    return $count ? $this->html_elements[$count - 1] : NULL;
  }
  function current_bbcode_element()
  {
    $count = count ($this->bbcode_elements);
    return $count ? $this->bbcode_elements[$count - 1] : NULL;
  }

  function state_opening_trivial ($tagname, $htmltag)
  {
    if (!$this->consume_if (']'))
    {
      return $this->flush_consumed();
    }

    $this->html_elements[] = HTML_append_element ($this->current_html_element(), $htmltag);
    $this->bbcode_elements[] = $tagname;

    return $this->discard_consumed();
  }
  function state_closing_trivial ($tagname)
  {
    if (!$this->consume_if ($tagname . ']'))
    {
      return $this->flush_consumed();
    }
    array_pop ($this->html_elements);
    array_pop ($this->bbcode_elements);

    return $this->discard_consumed();
  }

  function state_opening_url()
  {
    if (!$this->consume_if ('rl='))
    {
      return $this->flush_consumed();
    }
    $url = $this->consume_until (']');
    if (!$this->consume_if (']'))
    {
      return $this->flush_consumed();
    }

    $a = HTML_append_element ($this->current_html_element(), 'a');
    $a->setAttribute ('href', $url);
    //! \todo Use CSS instead..
    $this->html_elements[] = HTML_append_element ($a, 'i');
    $this->bbcode_elements[] = 'url';

    return $this->discard_consumed();
  }

  function state_opening_pet()
  {
    if (!$this->consume_if ('et='))
    {
      return $this->flush_consumed();
    }
    $id = $this->consume_while ('ctype_digit');
    if (!$this->consume_if(']'))
    {
      return $this->flush_consumed();
    }

    $localized = '';
    try
    {
      $localized = Localization_pet_name ($id, $this->language);
    }
    catch (Exception $ex)
    {
      return $this->flush_consumed();
    }

    HTML_append_text
      (HTML_append_element ($this->current_html_element(), 'b'), $localized);

    //! \note No pushing as this is open-only.

    return $this->discard_consumed();
  }
  function state_opening_skill()
  {
    if (!$this->consume_if ('kill='))
    {
      return $this->flush_consumed();
    }
    $id = $this->consume_while ('ctype_digit');
    if (!$this->consume_if (']'))
    {
      return $this->flush_consumed();
    }

    $localized = '';
    try
    {
      $localized = Localization_spell_name ($id, $this->language);
    }
    catch (Exception $ex)
    {
      return $this->flush_consumed();
    }

    HTML_append_text
      (HTML_append_element ($this->current_html_element(), 'b'), $localized);

    //! \note No pushing as this is open-only.

    return $this->discard_consumed();
  }

  function state_opening()
  {
    switch ($this->consume())
    {
    case 'b':
      return $this->state_opening_trivial ('b', 'b');
    case 'u':
      if ($this->peek() === ']')
      {
        return $this->state_opening_trivial ('u', 'u');
      }
      else
      {
        return $this->state_opening_url();
      }
    case 'i':
      return $this->state_opening_trivial ('i', 'i');
    case 's':
      if ($this->peek() === ']')
      {
        return $this->state_opening_trivial ('s', 's');
      }
      else
      {
        return $this->state_opening_skill();
      }

    case 'p':
      return $this->state_opening_pet();

    case '/':
      return $this->state_closing();

    default:
      return $this->flush_consumed();
    }
  }
  function state_closing()
  {
    switch ($this->current_bbcode_element())
    {
    case 'b':
      return $this->state_closing_trivial ('b');
    case 'u':
      return $this->state_closing_trivial ('u');
    case 'i':
      return $this->state_closing_trivial ('i');
    case 's':
      return $this->state_closing_trivial ('s');

    case 'url':
      return $this->state_closing_trivial ('url');

    default:
      return $this->flush_consumed();
    }
  }

  function state_normal()
  {
    while ($this->remaining)
    {
      switch ($this->peek())
      {
      case '[':
        $this->flush_consumed();
        $this->consume();
        $this->state_opening();
        break;

      case PHP_EOL:
        $this->flush_consumed();
        $this->consume();
        $this->discard_consumed();
        HTML_append_element ($this->current_html_element(), 'br');
        break;

      default:
        $this->consume();
        break;
      }
    }

    return $this->flush_consumed();
  }
};

function append_to_HTML ($root, $text, $language, $current_tag = NULL)
{
  $parser = new Parser ($language);
  $parser->start ($root, $text);
}
