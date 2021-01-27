<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewCustomer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $customer;
    protected $password;
    protected $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $password, $link)
    {
        $this->customer = $user;
        $this->password = $password;
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email.newCustomer')->with(
            [
                'customerName' => $this->customer->first_name . " " . $this->customer->last_name,
                'customerNo' => $this->customer->customer_no,
                'customerPassword' => $this->password,
                'customerDashboardLink' => $this->link
            ]
        );
    }
}
