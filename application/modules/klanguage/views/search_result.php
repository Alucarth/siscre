<?php foreach( $matches as $match ):?>

<?php $lang = $this->lang->load(basename($match), "en", true); ?>

<div style="margin-bottom: 12px;">
    <div><a href="javascript:void(0)" class="btn-search-goto" data-lang-name="<?=basename(str_replace("_lang.php", "", $match));?>"><?=ucwords(basename(str_replace("_lang.php", "", $match)));?></a></div>
    <div style="border: 1px solid #ccc;
    padding: 9px;
    border-radius: 4px;
    background: aliceblue;">
        <?php 
        foreach($lang as $key => $value)
        {
            if (stripos($value, $keywords) !== false) 
            {
                echo '$lang['.$key . "] = " . $value . "<br/>";
            }
            else
            {
                continue;
            }
        }
        ?>
    </div>
</div>

<?php endforeach; ?>


