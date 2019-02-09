<?php

if (!function_exists('currency')) {
    /**
     * Generate an currency path for the application.
     *
     * @param  string $amount
     * @param  bool $symbol
     * @return string
     */
    function currency($amount, $symbol)
    {
        $symbol = $symbol ?: Setting::get('currency', '₹');
        $amount = substr_replace($amount, '.', strlen($amount) - 2, 0);
        return $symbol . $amount;
    }
}

if (!function_exists('currencydecimal')) {
    /**
     * Generate an currency path for the application.
     *
     * @param  string $amount
     * @param  bool $symbol
     * @return string
     */
    function currencydecimal($amount)
    {
        $symbol = Setting::get('currency', '₹');
//        $amount = number_format($amount, 2, '.', '');
        return $symbol . $amount;
    }
}

if (!function_exists('roundPrice')) {
    /**
     * Show the price of a Product and N/A if not defined.
     *
     * @param  ProductPrice $path
     * @return string
     */

    function roundPrice($price)
    {
        $intVal = intval($price);
        if ($price - $intVal < .50) {
            return $intVal;
        } else {
            return $intVal + 1;
        }
    }
}

if (!function_exists('price')) {
    /**
     * Show the price of a Product and N/A if not defined.
     *
     * @param  ProductPrice $path
     * @return string
     */
    function price($prices)
    {
        return $prices->isEmpty() ? "N/A" : currency($prices[0]->price, $prices[0]->currency);
    }
}

if (!function_exists('image')) {
    /**
     * Show the image of a Product and placeholder if not defined.
     *
     * @param  Productimage $path
     * @param  bool $secure
     * @return string
     */
    function image($images, $secure = false)
    {
        return asset($images->isEmpty() ? 'images/placeholder.jpg' : $images[0]->url, $secure);
    }
}

if (!function_exists('status_array')) {
    /**
     * Show the image of a Product and placeholder if not defined.
     *
     * @param  Productimage $path
     * @return string
     */
    function status_array()
    {
        $status_array = [
            'CANCELLED',            // Request was cancelled by hotel or user
            'RECEIVED',             // Order recieved and is waiting to be acknowledged by hotel.
            'PROCESSING',           // Hotel has accepted the order and is processing the request
            'REACHED',              // Transporter reached hotel and waiting for pickup
            'PICKEDUP',             // Transporter has picked up the package and moving to delivery location
            'ARRIVED',              // Food is at users doorstep
            'COMPLETED',            // Order has been delivered and completed the request
        ];
        return $status_array;
    }
}

if (!function_exists('status_next')) {
    /**
     * Show the image of a Product and placeholder if not defined.
     *
     * @param  Productimage $path
     * @return string
     */
    function status_next($status)
    {
        $status_array = status_array();

        if ($status == 'CANCELLED') {
            return null;
        }

        try {
            $next = $status_array[array_search($status, $status_array) + 1];
            return $next;
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('send_sms')) {
    /**
     * Send the OTP to a phone number with country code.
     *
     * @param  Phone number     $phone
     * @return string
     */
    function send_sms($data)
    {
        $phone = $data['phone'];
        $newotp = isset($data['otp']) ? $data['otp'] : rand(100000, 999999);
        $message = 'Your OTP is : ' . $newotp;
        try {

            $msg = Twilio::message($phone, $message);
            \Log::info($msg);
            return $msg->error_message;
        } catch (\Services_Twilio_RestException $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('curl')) {
    /**
     * get the http request of a url
     *
     * @param  URL $url
     * @return array
     */
    function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
}

if (!function_exists('upload_image')) {

    function upload_image($picture)
    {
        $file_name = time();
        $file_name .= rand();
        $file_name = sha1($file_name);
        if ($picture) {
            $ext = $picture->getClientOriginalExtension();
            $picture->move(public_path() . "/uploads", $file_name . "." . $ext);
            $local_url = $file_name . "." . $ext;

            $s3_url = url('/') . '/uploads/' . $local_url;

            return $s3_url;
        }
        return "";
    }
}

if (!function_exists('remove_image')) {

    function remove_image($picture)
    {
        File::delete(public_path() . "/uploads/" . basename($picture));
        return true;
    }
}

if (!function_exists('recurse_copy')) {
    function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
if (!function_exists('site_sendmail')) {
    function site_sendmail($user)
    {

        $site_details = Setting::all();
        Mail::send('emails.invoice', ['user' => $user, 'site_details' => $site_details], function ($mail) use ($user, $site_details) {
            // $mail->from('harapriya@appoets.com', 'Your Application');

            $mail->to($user->user->email, $user->user->first_name . ' ' . $user->user->last_name)->subject('Invoice');
        });

        return true;
    }
}


?>