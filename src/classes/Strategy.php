<?php namespace Strategy; require_once ($_SERVER['DOCUMENT_ROOT'] . '/preamble.php');

require_once ('Database.php');

/*main selector, sub selector*/ function delete ($id, $user)
{
  if (!$user)
  {
    throw new \Exception ('delete but user not logged in');
  }

  $strat = Database_SELECT_object ('Alternatives', 'WHERE id = ?', 'i', $id);

  if ($user->id != $strat->User && !User_is_allowed ($user, 'DeleteStrats'))
  {
    throw new \Exception ('delete but no permission');
  }

  Database_UPDATE
    ( 'Comments '
    , [ 'Closed' => 1
      , 'CloseType' => '"Strategy Deleted by Creator"'
      , 'ClosedOn' => 'NOW()'
      , 'Deleted' => 1
      , 'NewActivity' => 0
      , 'ForReview' => 0
      , 'ClosedBy' => '"Strategy Creator"'
      ]
    , 'WHERE Category = 2 AND SortingID = ?'
    , 'i'
    , $strat->id
    );

  Database_DELETE ('Strategy', 'WHERE SortingID = ?', 'i', $strat->id);
  Database_DELETE ('UserAttempts', 'WHERE Strategy = ?', 'i', $strat->id);
  Database_DELETE ('UserFavStrats', 'WHERE Strategy = ?', 'i', $strat->id);
  Database_DELETE ('UserStratRating', 'WHERE Strategy = ?', 'i', $strat->id);
  Database_DELETE ('Alternatives', 'WHERE id = ?', 'i', $strat->id);
  Database_DELETE ('Strategy_x_Tags', 'WHERE Strategy = ?', 'i', $strat->id);

  return [$strat->Main, $strat->Sub];
}
