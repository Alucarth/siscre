<button class="btn btn-primary tbl_doc_list_dt-custom-button" type="button" id="btn-upload-collateral-doc"><?= ktranslate2("Upload") ?></button>
<table class="table table-hover table-bordered" id="tbl_doc_list">
    <thead>
        <tr>
            <th style="text-align: center; width: 1%"></th>
            <th style="text-align: center;"><?= ktranslate2("Document Name") ?></th>
            <th style="text-align: center;"><?= ktranslate2("Description") ?></th>
            <th style="text-align: center;"><?= ktranslate2("Modified Date") ?></th>
        </tr>
    </thead>
</table>
<?= $tbl_doc_list; ?>