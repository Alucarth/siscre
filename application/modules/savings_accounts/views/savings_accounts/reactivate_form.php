<?php $this->load->view('partial/header'); ?>

<div class="title-block">
    <h3 class="title">Reactivar cuenta de ahorro</h3>
    <p class="title-description">Indica un motivo para reactivar esta cuenta.</p>
</div>

<div class="section">
    <div class="row sameheight-container">
        <div class="col-lg-6 offset-lg-3 col-md-8 offset-md-2">
            <div class="card">
                <div class="card-block">

                    <?= form_open(site_url('savings_accounts/savings_accounts/reactivate/' . (int)$account_id), ['method' => 'post']) ?>

                        <div class="form-group">
                            <label for="reason">Motivo de reactivación:</label>
                            <textarea name="reason" id="reason" rows="4" class="form-control" required placeholder="Describe brevemente el motivo..."></textarea>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-success">
                                <span class="glyphicon glyphicon-ok"></span> Reactivar cuenta
                            </button>
                            <a href="<?= site_url('savings_accounts/savings_accounts/inactive') ?>" class="btn btn-secondary">
                                <span class="glyphicon glyphicon-remove"></span> Cancelar
                            </a>
                        </div>

                    <?= form_close(); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('partial/footer'); ?>
