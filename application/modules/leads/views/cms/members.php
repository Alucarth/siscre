<style>
    #leads_orders_wrapper table td:nth-child(5),
    #leads_orders_wrapper table td:nth-child(6)
    {
        text-align: center;
    }
    #leads_orders_wrapper table td:nth-child(7),
    #leads_orders_wrapper table td:nth-child(8),
    #leads_orders_wrapper table th {
        text-align: center
    }
    #leads_orders_wrapper table tbody > tr:hover {
        background: #ececec;
    }
    .dataTables_scroll {
        width: 100% !important;
    }
    
    .dataTables_scrollBody {
        max-height: fit-content !important;
        height: auto !important;
    }
</style>

<table class="table table-bordered" id="leads_orders">
    <thead>
        <tr>
            <th></th>
            <th>Full name</th>
            <th>Email</th>
            <th>Occupation</th>                            
            <th>Signup Date</th>
        </tr>
    </thead>        
</table>

<?= $tbl_leads_orders; ?>