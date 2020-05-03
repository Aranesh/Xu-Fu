<?php

function HTML_create_root ($element)
{
  $root = new DOMDocument ('1.0', 'utf-8');
  $root->formatOutput = true;
  return $root->createElement ($element);
}

function HTML_to_string ($what)
{
  $owner = $what->ownerDocument;
  $divs = $what->getElementsByTagName ('div');
  foreach ($divs as $div)
  {
    if (!$div->childNodes->length)
    {
      $div->appendChild ($owner->createTextNode (''));
    }
  }
  return $owner->saveXML ($what);
}

function HTML_append_element ($where, $element)
{
  $next = $where->ownerDocument->createElement ($element);
  $where->appendChild ($next);
  return $next;
}
function HTML_append_elements ($where, $elements)
{
  foreach ($elements as $element)
  {
    $where = HTML_append_element ($where, $element);
  }
  return $where;
}

function HTML_append_text ($where, $text)
{
  $where->appendChild
    ( $where->ownerDocument->createTextNode
        (html_entity_decode ($text, ENT_QUOTES | ENT_HTML5))
    );
}
function HTML_append_paragraph_text ($where, $text, $class = 'blogeven')
{
  $element = HTML_append_element ($where, 'p');
  if ($class !== FALSE)
  {
    $element->setAttribute ('class', $class);
  }
  HTML_append_text ($element, $text);
  return $element;
}

function HTML_append_label ($where, $for)
{
  $elem = HTML_append_element ($where, 'label');
  $elem->setAttribute ('for', $for);
  return $elem;
}

function HTML_append_link_with_text ($where, $text, $href, $target = FALSE)
{
  $a = HTML_append_element ($where, 'a');

  $a->setAttribute ('href', $href);

  if ($target !== FALSE)
  {
    $a->setAttribute ('target', $target);
  }

  HTML_append_text ($a, $text);

  return $a;
}

function HTML_append_spacer_image ($where, $path, $width, $height)
{
  $path[] = 'img';
  $x = HTML_append_elements ($where, $path);
  $x->setAttribute ('src', '/images/blank.png');
  $x->setAttribute ('width', $width);
  $x->setAttribute ('height', $height);
}

function HTML_append_form ($where, $action, $method, $name = FALSE)
{
  $form = HTML_append_element ($where, 'form');
  if ($name !== FALSE)
  {
    $form->setAttribute ('name', $name);
  }
  $form->setAttribute ('action', $action);
  $form->setAttribute ('method', $method);
  return $form;
}

function HTML_append_hidden_form_input ($where, $name, $value)
{
  $input = HTML_append_element ($where, 'input');
  $input->setAttribute ('type', 'hidden');
  $input->setAttribute ('name', $name);
  $input->setAttribute ('value', $value);
  return $input;
}
