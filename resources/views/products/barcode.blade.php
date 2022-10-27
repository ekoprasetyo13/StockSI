<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistem Inventory</title>
    <!-- Tell the browser to be responsive to screen width -->
   
    <style>
        .page-break {
            page-break-after: always;
        }
        .container {
            width: 16.5cm; /* 7in */
            margin-right: 4cm;
            /* width: 6.5cm; */
            /* margin-right: 2in; */
            /* margin-top: 0.3cm; */
        }

        .badge {
            
            display: inline-block;
            margin-left: 1.3cm;
            
            width: 6cm; /* 1.9 */
            height: 1cm;
            /* width: 0.2cm; */
        }
    </style>
    <body>
    
    <div class="container">
        @php 
        $a=1;
        @endphp
        @foreach($product as $pr)
            <div class="badge">
                {!! DNS1D::getBarcodeHTML($pr->product_code, 'C128', true) !!}
                <p class="text" style="margin-top: 2px">( {{$pr->product_code}} )</p>
            </div>
            @if($a%2 == 0)
            <br>
            <br>
            <br>
            <br style="margin-top: 1cm;">
            @endif

            @php 
            $a++;
           
            @endphp
            @if($a%16 == 1)
            <!-- <p>OK</p> -->
            <div class="page-break"></div>
            @endif
            
        @endforeach
    
    </div>
    </body>
</html
