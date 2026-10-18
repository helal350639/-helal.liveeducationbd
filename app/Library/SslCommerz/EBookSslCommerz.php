<?php
namespace App\Library\SslCommerz;

/** Isolated E-Book gateway callbacks; inherits existing SSLCommerz merchant credentials. */
class EBookSslCommerz extends SslCommerzNotification
{
    public function setRequiredInfo(array $info)
    {
        parent::setRequiredInfo($info);
        $this->data['success_url']=route('lebd.ebook.payment.success');
        $this->data['fail_url']=route('lebd.ebook.payment.fail');
        $this->data['cancel_url']=route('lebd.ebook.payment.cancel');
        $this->data['ipn_url']=route('lebd.ebook.payment.ipn');
        return $this->data;
    }
}
