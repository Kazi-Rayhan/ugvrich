<?php

/*
 | Workflow event wording, shared by the notification list and the emails.
 | Kept apart from researcher.php because these are sentences about events
 | rather than labels on a screen.
 */

return [
    'events' => [
        'idea.submitted' => 'Your research idea was submitted',
        'idea.approved' => 'Your research idea was approved',
        'idea.revision' => 'A revision was requested on your research idea',
        'idea.rejected' => 'Your research idea was not taken forward',
        'idea.recommended' => 'Your research idea was recommended for a proposal',

        'proposal.submitted' => 'Your proposal was submitted',
        'proposal.approved' => 'Your research proposal was approved',
        'proposal.revision' => 'A revision was requested on your proposal',
        'proposal.rejected' => 'Your research proposal was not taken forward',

        'funding.decided' => 'A funding decision was recorded',
        'project.created' => 'A research project was created from your proposal',
        'project.status' => 'Your project status was changed',

        'manuscript.submitted' => 'Your manuscript was submitted',
        'manuscript.review' => 'Your manuscript is under internal review',
        'manuscript.revision' => 'A revision was requested on your manuscript',
        'manuscript.approved' => 'Your manuscript was approved',
        'manuscript.accepted' => 'Your manuscript was accepted',
        'manuscript.published' => 'Your manuscript was published',
        'manuscript.rejected' => 'Your manuscript was not approved',
    ],

    'mail' => [
        'greeting' => 'Hello :name,',
        'action' => 'Open it in the portal',
        'sign_off' => 'UGV RICH Research Wing',
    ],

    'notifications' => [
        'title' => 'Notifications',
        'none' => 'Nothing yet.',
        'none_lead' => 'Decisions on your ideas, proposals, funding, projects and manuscripts will appear here.',
        'mark_all' => 'Mark all as read',
        'marked' => 'Notifications marked as read.',
        'unread' => 'Unread',
        'view' => 'Open',
    ],
];
