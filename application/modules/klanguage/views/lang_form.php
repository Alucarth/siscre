<style>
    .cont-77xz1 {
        border: 1px solid #ccc;
        padding: 4px;
        margin-bottom: 18px;
    }
    .cont-77xz2 {
        border: 1px solid #ccc;
        width: 100%;
        display: inline;
        padding: 0 8px 3px 8px;
        border-top-right-radius: 8px;
        background: #fbfbfb;
        font-size: 11px;
        text-transform: uppercase;
    }
</style>
<div id="tbl-lang-form">
    <?php foreach ($lang as $key => $r): ?>
        <?php if (trim($r) != ''): ?>
            <div>
                <div class="cont-77xz2"><a href="javascript:void(0)" class="btn-remove-lang" style="color:#e80e0e"><span class="fa fa-trash"></span></a> <?= $key; ?></div>
                <div class="cont-77xz1">
                    <textarea class="form-control" style="min-height:50px;" name="lang[<?= $key ?>]"><?= $r ?></textarea>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
