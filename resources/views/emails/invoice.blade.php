

<div>

<div>

	<div>
	Booking Id
	</div>
	<div>
    {{$user->id}}
    </div>
</div>
<div>

	<div>
Driver Name
</div>
	<div>
    {{@$user->transporter->name}}  
    </div>
</div>
<div>

	<div>
Vehicle Number
</div>
	<div>
     {{@$user->vehicles->vehicle_no}}

    </div>
</div>
 
<div>

	<div>
Payment Mode

</div>
	<div>
    {{$user->invoice->payment_mode}}

    </div>
</div>
    <div>

	<div>
Payment Status
</div>
	<div>
    @if($user->invoice->paid==1)
    Paid
    @else
    Unpaid
    @endif
    </div>
</div>

<h3>Invoice</h3>
<div>

	<div>
Base Fare
</div>
	<div>
    {{currencydecimal($user->invoice->gross)}}
    </div>
</div>
    <div>

	<div>
Delivery Fare
</div>
	<div>
    {{currencydecimal($user->invoice->delivery_charge)}}
    </div>
</div>
    <div>

	<div>
Tax Fare
</div>
	<div>
     {{currencydecimal($user->invoice->tax)}}
    </div>
</div>
    <div>

	<div>
Total
</div>
	<div>
     {{currencydecimal($user->invoice->net)}} 
    </div>
</div>

    </div>