<ul class="progress_line">
    <li class="progress_line__item <?=$step_status[1];?>" role="progressLineItem">
        <a href="<?= $step_url[1]; ?>">
            <div class="progress_line__count">1</div>
            <div class="progress_line__text">Personal information</div>
        </a>
    </li>
    <li class="progress_line__item <?=$step_status[2];?>" role="progressLineItem">
        <?php if ( $step_url[2] != '' ): ?>
            <a href="<?= $step_url[2]; ?>">
                <div class="progress_line__count">2</div>
                <div class="progress_line__text">Address details</div>
            </a>
        <?php else: ?>
            <div class="progress_line__count">2</div>
            <div class="progress_line__text">Address details</div>
        <?php endif; ?>
    </li>
    <li class="progress_line__item <?=$step_status[3];?>" role="progressLineItem">
        <?php if ( $step_url[3] != '' ): ?>
            <a href="<?= $step_url[3]; ?>">
                <div class="progress_line__count">3</div>
                <div class="progress_line__text">Employment details</div>
            </a>
        <?php else: ?>
            <div class="progress_line__count">3</div>
            <div class="progress_line__text">Employment details</div>
        <?php endif; ?>
    </li>
    <li class="progress_line__item <?=$step_status[4];?>" role="progressLineItem">
        <?php if ( $step_url[4] != '' ): ?>
            <a href="<?= $step_url[4]; ?>">
                <div class="progress_line__count">4</div>
                <div class="progress_line__text">Documents</div>
            </a>
        <?php else: ?>
            <div class="progress_line__count">4</div>
            <div class="progress_line__text">Documents</div>
        <?php endif; ?>
    </li>
    <li class="progress_line__item progress_line__item-disabled <?=$step_status[5];?>" role="progressLineItem">
        <?php if ( $step_url[5] != '' ): ?>
            <a href="<?= $step_url[5]; ?>">
                <div class="progress_line__count">5</div>
                <div class="progress_line__text">Disbursement</div>
            </a>
        <?php else: ?>
            <div class="progress_line__count">5</div>
            <div class="progress_line__text">Disbursement</div>
        <?php endif; ?>
    </li>
</ul>