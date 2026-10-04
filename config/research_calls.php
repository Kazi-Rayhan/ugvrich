<?php

/*
 | The annual faculty research funding cycle.
 |
 | Proposals are unlimited. Each year the Research Wing announces the top
 | `shortlist_min` to `shortlist_max` proposals in every faculty, and
 | `funded_per_faculty` of them is funded. The Research page draws its figures
 | from here, so a change in policy is a change to these numbers, not to the
 | page. Faculty names live in lang/{en,bn}/research_hub.php under
 | `calls.faculties`, keyed the same way.
 */

return [

    'shortlist_min' => 2,
    'shortlist_max' => 4,
    'funded_per_faculty' => 1,

    'faculties' => [
        'engineering' => 'cpu',
        'business' => 'briefcase',
        'health' => 'heart',
        'humanities' => 'users',
    ],

];
