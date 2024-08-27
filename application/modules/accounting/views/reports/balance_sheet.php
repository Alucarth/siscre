<style>
    .center-text {
        text-align:center;
    }
</style>

<div style="text-align:center">
    <h3>Hoja de saldos</h3>
   Fechado: <?=date($this->config->item('date_format'), $date_from) . " - " . date($this->config->item('date_format'), $date_to)?>
    <br/>
    <br/>
</div>

<table width="100%" cellpadding="1" cellspacing="0" border="0" id="tbl-balance-sheet">
    <tr>
        <td style="width:50%" valign="top">
            <div><b>ACTIVO</b></div>    
            <table width="100%" cellpadding="1" cellspacing="0" border="1">
                <tr>
                    <td colspan="2">Activos Corrientes</td>
                </tr>
                <tr>
                    <td style="width:70%">Efectivo</td>
                    <td class="center-text"><?=to_currency($cash_amount);?></td>
                </tr>
                <tr>
                    <td>Efectivo en bancos</td>
                    <td class="center-text"><?=to_currency($cash_amount_bank)?></td>
                </tr>
                <tr>
                    <td colspan="2">Prestamos pendientes</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prestamo actual</td>
                    <td class="center-text"><?=to_currency($current_loan_amount);?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Interes por cobrar</td>
                    <td class="center-text"><?=to_currency($interest_on_current);?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MENOS: Reservas por Prestamos Incobrables</td>
                    <td class="center-text"><?=to_currency($loan_loss_reserve)?></td>
                </tr>
                <tr>
                    <td>Prestamos Netos Pendientes</td>
                    <td class="center-text"><?=to_currency($net_loan_outstanding);?></td>
                </tr>
                <tr>
                    <td><b>TOTAL ACTIVOS ACTUALES</b></td>
                    <td class="center-text"><b><?=to_currency($total_current_assets);?></b></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">ACTIVOS No Corrientes</td>
                </tr>
                
                <?php $total_non_current_assets = 0; ?>
                
                <?php foreach( $non_current_assets as $asset ): ?>
                    <tr>
                        <td><?=$asset->account_name;?></td>
                        <td class="center-text"><?=to_currency($asset->amount);?></td>
                    </tr>
                    <?php $total_non_current_assets += $asset->amount;?>
                    
                    <?php if ( $asset->depreciation_amount > 0 ): ?>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;Menos: Depreciacion Acumulada</td>
                        <td class="center-text">(<?=to_currency($asset->depreciation_amount);?>)</td>
                    </tr>
                    <?php $total_non_current_assets -= $asset->depreciation_amount;?>
                    <?php endif; ?>
                
                
                <?php endforeach; ?>
                <tr>
                    <td><b>TOTAL ACTIVOS NO CORRIENTES</b></td>
                    <td class="center-text"><b><?=to_currency($total_non_current_assets);?></b></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><b>TOTAL ACTIVOS</b></td>
                    
                    <?php
                    
                    $total_assets = $total_current_assets + $total_non_current_assets;
                    
                    ?>
                    
                    <td class="center-text"><b><?=to_currency($total_assets);?></b></td>
                </tr>
            </table>
        </td>
        <td style="width:50%" valign="top">
            <div><b>PASIVOS</b></div>
            
            <?php $loan_fund_capital = $total_assets;?>
            <?php $total_liability = 0;?>
            
            <table width="100%" cellpadding="1" cellspacing="0" border="1">
                <?php foreach( $liability_accounts as $account ): ?>
                <tr>
                    <td style="width:70%"><?=$account->account_name;?></td>
                    <td class="center-text"><?=to_currency($account->amount);?></td>
                </tr>
                <?php $total_liability += $account->amount;?>
                <?php endforeach; ?>
                <tr>
                    <td style="width:70%"><b>TOTAL PASIVOS</b></td>
                    <td class="center-text"><b><?=to_currency($total_liability)?></b></td>
                </tr>
            </table>
            <br/>
            
            <?php
            
            $loan_fund_capital -= $total_liability;
            foreach ( $equity_accounts as $account )
            {
                $loan_fund_capital -= $account->amount;
            }
            
            ?>
            
            <div><b>PATRIMONIO</b></div>
            <table width="100%" cellpadding="1" cellspacing="0" border="1">
                <tr>
                    <td style="width:70%">Fondo de Capital</td>
                    <td class="center-text"><?=to_currency($loan_fund_capital);?></td>
                </tr>
                <?php $total_capital = 0;?>
                <?php foreach ( $equity_accounts as $account ):?>
                <tr>
                    <td style="width:70%"><?=$account->account_name;?></td>
                    <td class="center-text"><?=to_currency($account->amount);?></td>
                </tr>
                
                <?php $total_capital += $account->amount;?>
                
                <?php endforeach; ?>
                <tr>
                    <td style="width:70%"><b>TOTAL PATRIMONIO</b></td>
                    <td class="center-text"><b><?=to_currency($total_capital + $loan_fund_capital);?></b></td>
                </tr>
            </table>
            
            <br/>
            <table width="100%" cellpadding="1" cellspacing="0" border="1">
                <tr>
                    <td style="width:70%"><b>TOTAL PASIVO Y CAPITAL</b></td>
                    <td class="center-text"><b><?=to_currency($total_liability + $total_capital + $loan_fund_capital)?></b></td>
                </tr>
            </table>
        </td>        
    </tr>
</table>