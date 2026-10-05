<?php

/*
 | The innovator dashboard: the account made when somebody submits an idea,
 | the set-password page its email links to, and the dashboard of their ideas.
 */

return [

    'portal' => 'Innovator Portal',
    'eyebrow' => 'Become an Innovator',
    'welcome' => 'Welcome, :name',
    'lead' => 'Every idea you submit is here, with where it has got to on the journey from idea to market.',
    'submit_another' => 'Submit another idea',
    'back_to_site' => 'Back to the website',
    'logout' => 'Sign out',

    'stats' => [
        'ideas' => 'Ideas submitted',
        'in_review' => 'In review',
        'accepted' => 'Accepted',
    ],

    'ideas_title' => 'My ideas',
    'empty_title' => 'No ideas yet',
    'empty_body' => 'Submit your first idea and follow it here.',
    'submitted' => 'Submitted :date',
    'journey' => 'Journey',
    'stage_of' => 'Stage :current of :total',
    'download' => 'Your file',
    'status' => [
        'new' => 'Received',
        'in_review' => 'In review',
        'accepted' => 'Accepted',
        'on_hold' => 'On hold',
        'declined' => 'Not taken forward',
    ],

    'password' => [
        'title' => 'Set your password',
        'lead' => 'Choose a password for your innovator account. You will sign in with your email and this password to follow your ideas.',
        'email' => 'Email',
        'new' => 'Password',
        'confirm' => 'Confirm password',
        'submit' => 'Set password and continue',
        'invalid' => 'This link has expired or has already been used. Submit an idea again from the same email, or contact the RICH office.',
        'done' => 'Your password is set. Welcome to your innovator dashboard.',
    ],

    'mail' => [
        'subject' => 'Your idea is with UGV RICH — set your password',
        'greeting' => 'Hello :name,',
        'received' => 'Thank you. Your idea ":title" has reached the UGV RICH Innovation Wing.',
        'account' => 'We have made you an innovator account, so you can follow your idea from your own dashboard. Choose a password to sign in:',
        'action' => 'Set my password',
        'expiry' => 'This link stays valid for 7 days.',
    ],

    'thanks' => [
        'new' => 'We have emailed :email a link to set your password. Use it to sign in and follow this idea from your innovator dashboard.',
        'existing' => 'This idea has been added to your innovator dashboard.',
        'dashboard' => 'Go to my dashboard',
    ],
];
