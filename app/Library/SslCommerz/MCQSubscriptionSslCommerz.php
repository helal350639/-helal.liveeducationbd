<?php
namespace App\Library\SslCommerz;

/** Uses existing merchant credentials but dedicated subscription callbacks. */
class MCQSubscriptionSslCommerz extends SslCommerzNotification
{
    public function setRequiredInfo(array $info)
    {
        parent::setRequiredInfo($info);
        $this->data['success_url'] = route('lebd.subscription.payment.success');
        $this->data['fail_url'] = route('lebd.subscription.payment.fail');
        $this->data['cancel_url'] = route('lebd.subscription.payment.cancel');
        $this->data['ipn_url'] = route('lebd.subscription.payment.ipn');
        return $this->data;
    }
}
