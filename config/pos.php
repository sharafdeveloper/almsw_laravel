<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currency symbol displayed throughout the POS UI
    |--------------------------------------------------------------------------
    */
    'currency' => 'Rs',

    /*
    |--------------------------------------------------------------------------
    | Company details printed on invoices (edit these to match your business)
    |--------------------------------------------------------------------------
    */
    'company' => [
        'name'     => 'AL-MAKKAH-STEEL WORKS',
        'tagline'  => 'Deals in H.R.C & C.R.C Sheets | Sheet Cutting & Banding',
        'address1' => 'Super Market, Plot No. A-102, Sec. 1-A, Madarsa Noor-ul-Huda Near Lal Masjid, Sohrab Goth, Super Highway, Karachi.',
        //'address2' => 'Suit # 22/135, Near K.M.C Work Shop, Old Haji Camp Road Karachi',
   
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre-populated city list for the customer dropdown
    | (also mirrored in CustomerController::CITIES — keep in sync if you edit)
    |--------------------------------------------------------------------------
    */
    'cities' => [
        'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan',
        'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala', 'Hyderabad', 'Bahawalpur',
        'Sargodha', 'Sukkur', 'Larkana', 'Sheikhupura', 'Mardan', 'Mirpur Khas',
        'Rahim Yar Khan', 'Kasur', 'Sahiwal', 'Okara', 'Wah Cantt', 'Dera Ghazi Khan',
        'Mingora', 'Nawabshah', 'Chiniot', 'Kotri', 'Kamoke', 'Hafizabad',
        'Sadiqabad', 'Mianwali', 'Tando Adam', 'Jaranwala', 'Khanewal', 'Burewala',
        'Kohat', 'Muzaffargarh', 'Khanpur', 'Gojra', 'Bahawalnagar', 'Muridke',
        'Pakpattan', 'Abottabad', 'Tando Allahyar', 'Jhang', 'Mansehra', 'Other',
    ],
];
