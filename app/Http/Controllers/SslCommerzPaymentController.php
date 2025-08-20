<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Http\Controllers\Students\StudentHomeCtrl;
use App\Http\Controllers\SourceCtrl;
use App\Models\Student;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Order;
use Session;

class SslCommerzPaymentController extends Controller
{
    protected $redirect_url; 
    protected $batch_id;

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    public function index(Request $request)
    {
        $redirect_url = route('students.my-course');
        $data = $request->all();

        //find batch
        $batch = Batch::find($request->batch_id);

        $course = $batch->course;

        //find department
        $department = Department::find($request->department_id);

        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "orders"
        # In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = $data['total']; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_student'] = $data['student_id'];
        $post_data['cus_batch'] = $data['batch_id'];
        $post_data['cus_department'] = $data['department_id'];
        $post_data['cus_name'] = $data['name'];
        $post_data['cus_email'] = $data['email'];
        $post_data['cus_add1'] = $data['address1'];
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $data['phone'];
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $batch->name;
        $post_data['product_category'] = $course->name;
        $post_data['product_profile'] = $department->name;

        #Before  going to initiate the payment order status need to insert or update as Pending.
        $update_product = DB::table('orders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'student_id' => $post_data['cus_student'],
                'batch_id' => $post_data['cus_batch'],
                'department_id' => $post_data['cus_department'],
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        $order_latest = DB::table('orders')
        ->where('student_id', $request->student_id)
        ->where('batch_id', $request->batch_id)
        ->where('department_id', $request->department_id)
        ->first();
        
        # OPTIONAL PARAMETERS
        $post_data['value_a'] = $order_latest->id;
        $post_data['value_b'] = $batch->name;
        $post_data['value_c'] = $department->name;
        $post_data['value_d'] = "";

        // dd($post_data);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function payViaAjax(Request $request)
    {
        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";


        #Before  going to initiate the payment order status need to update as Pending.
        $update_product = DB::table('orders')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency']
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function success(Request $request)
    {
        $source = new SourceCtrl;
        $studentctrl = new StudentHomeCtrl;

        // echo "Transaction is Successful";

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        $order_details = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount', 'student_id', 'batch_id', 'department_id')->first();
        
        // $order = Order::find($request->value_a);
        $redirect_url = route('students.course.show', $order_details->batch_id);
        $redirect_script = "<script> setTimeout('window.location.href=\"".$redirect_url."\"', 100);</script>";

        if($order_details)
        {
          /** add student to the batch and department */
          //find student by ordered student id
          $student = Student::find($order_details->student_id);

          //synchronize batch by student id
          // if(!$student->batches()->where('batch_id', $order_details->batch_id)->count())
          // {
            $student->batches()->attach($order_details->batch_id);
          // }
          
          DB::table('batch_student')
          ->where('student_id', $order_details->student_id)
          ->updateOrInsert([
            'batch_id' => $order_details->batch_id,
            'student_id' => $order_details->student_id
          ]);

          //synchronize department by student id
          DB::table('department_student')
          ->where('student_id', $order_details->student_id)
          ->updateOrInsert([
            'department_id' => $order_details->department_id,
            'student_id' => $order_details->student_id
          ]);

          //send sms
          if($student->contact)
          {
              $source->sms_send('88'.$student->contact, 'Your course has been successfully purchased. For more information, please visit liveeducationbd.com');
          }

          //send email
          if($student->email)
          {
            $data = [
              'email_to' => $student->email,
              'subject' => 'New Course Purchase | Live Education BD',
              'comments' => 'Hello '.$student->name.'<br> Your course has been successfully purchased. For more information, visit <a href="'.route('home.course.show', $order_details->batch_id).'">liveeducationbd.com</a>'
            ];
            $source->sendMail($data);
          }
        }

        if ($order_details->status == 'Pending') {
          $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);

          if ($validation)
          {
            /*
            That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
            in order table as Processing or Complete.
            Here you can also sent sms or email for successfull transaction to customer
            */
            $update_product = DB::table('orders')
              ->where('transaction_id', $tran_id)
              ->update(['status' => 'Processing']);

            echo "<br> Transaction is successfully Completed. It will automatic redirect to you ... ";

            //redirect to homepage
            echo $redirect_script;
          }
        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {
          /*
          That means through IPN Order status already updated. Now you can just show the customer that transaction is completed. No need to udate database.
          */
          echo "Transaction is successfully Completed";
          //redirect to homepage
          echo $redirect_script;
        } else {
          #That means something wrong happened. You can redirect customer to your product page.
          echo "Invalid Transaction";
          //redirect to homepage
          echo $redirect_script;
        }
    }

    public function fail(Request $request)
    {
        $redirect_url = route('students.my-course');
        $redirect_script = "<script> setTimeout('window.location.href=\"".$redirect_url."\"', 5000);</script>";
        
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('orders')
          ->where('transaction_id', $tran_id)
          ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == 'Pending') {
          $update_product = DB::table('orders')
          ->where('transaction_id', $tran_id)
          ->update(['status' => 'Failed']);

          echo "Transaction is Failed";

          //redirect to homepage
          echo $redirect_script;

        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
            //redirect to homepage
            echo $redirect_script;
        } else {
            echo "Transaction is Invalid";
            //redirect to homepage
            echo $redirect_script;
        }
    }

    public function cancel(Request $request)
    {
        $redirect_url = route('students.my-course');
        $redirect_script = "<script> setTimeout('window.location.href=\"".$redirect_url."\"', 5000);</script>";

        $tran_id = $request->input('tran_id');

        $order_details = DB::table('orders')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == 'Pending') {
            $update_product = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->update(['status' => 'Canceled']);

            echo "Transaction is Cancel";

            //redirect to homepage
            echo $redirect_script;

        } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

            echo "Transaction is already Successful";

            //redirect to homepage
            echo $redirect_script;

        } else {

            echo "Transaction is Invalid";

            //redirect to homepage
            echo $redirect_script;

        }
    }

    public function ipn(Request $request)
    {
        $redirect_url = route('students.my-course');
        $redirect_script = "<script> setTimeout('window.location.href=\"".$redirect_url."\"', 5000);</script>";

        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                    //redirect to homepage
                    echo $redirect_script;
                }
            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
                //redirect to homepage
                echo $redirect_script;
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
                //redirect to homepage
                echo $redirect_script;
            }
        } else {
            echo "Invalid Data";
            //redirect to homepage
            echo $redirect_script;
        }
    }

}
