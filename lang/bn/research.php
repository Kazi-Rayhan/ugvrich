<?php

/*
 | Workflow event wording, shared by the notification list and the emails.
 | Kept apart from researcher.php because these are sentences about events
 | rather than labels on a screen.
 */

return [
    'events' => [
        'idea.submitted' => 'আপনার গবেষণা ধারণা জমা হয়েছে',
        'idea.approved' => 'আপনার গবেষণা ধারণা অনুমোদিত হয়েছে',
        'idea.revision' => 'আপনার গবেষণা ধারণায় সংশোধন চাওয়া হয়েছে',
        'idea.rejected' => 'আপনার গবেষণা ধারণাটি এগিয়ে নেওয়া হয়নি',
        'idea.recommended' => 'আপনার ধারণাটি প্রস্তাব তৈরির জন্য সুপারিশ করা হয়েছে',

        'proposal.submitted' => 'আপনার প্রস্তাব জমা হয়েছে',
        'proposal.approved' => 'আপনার গবেষণা প্রস্তাব অনুমোদিত হয়েছে',
        'proposal.revision' => 'আপনার প্রস্তাবে সংশোধন চাওয়া হয়েছে',
        'proposal.rejected' => 'আপনার প্রস্তাবটি এগিয়ে নেওয়া হয়নি',

        'funding.decided' => 'অর্থায়ন সংক্রান্ত সিদ্ধান্ত নথিভুক্ত হয়েছে',
        'project.created' => 'আপনার প্রস্তাব থেকে গবেষণা প্রকল্প তৈরি হয়েছে',
        'project.status' => 'আপনার প্রকল্পের অবস্থা পরিবর্তিত হয়েছে',

        'manuscript.submitted' => 'আপনার পাণ্ডুলিপি জমা হয়েছে',
        'manuscript.review' => 'আপনার পাণ্ডুলিপি অভ্যন্তরীণ পর্যালোচনায় আছে',
        'manuscript.revision' => 'আপনার পাণ্ডুলিপিতে সংশোধন চাওয়া হয়েছে',
        'manuscript.approved' => 'আপনার পাণ্ডুলিপি অনুমোদিত হয়েছে',
        'manuscript.accepted' => 'আপনার পাণ্ডুলিপি গৃহীত হয়েছে',
        'manuscript.published' => 'আপনার পাণ্ডুলিপি প্রকাশিত হয়েছে',
        'manuscript.rejected' => 'আপনার পাণ্ডুলিপি অনুমোদিত হয়নি',
    ],

    'mail' => [
        'greeting' => 'হ্যালো :name,',
        'action' => 'পোর্টালে দেখুন',
        'sign_off' => 'ইউজিভি রিচ গবেষণা শাখা',
    ],

    'notifications' => [
        'title' => 'বিজ্ঞপ্তি',
        'none' => 'এখনো কিছু নেই।',
        'none_lead' => 'আপনার ধারণা, প্রস্তাব, অর্থায়ন, প্রকল্প ও পাণ্ডুলিপি সংক্রান্ত সিদ্ধান্ত এখানে দেখা যাবে।',
        'mark_all' => 'সবগুলো পঠিত হিসেবে চিহ্নিত করুন',
        'marked' => 'বিজ্ঞপ্তিগুলো পঠিত হিসেবে চিহ্নিত হয়েছে।',
        'unread' => 'অপঠিত',
        'view' => 'খুলুন',
    ],
];
