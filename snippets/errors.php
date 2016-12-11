<?php if (count($form->errors()) > 0): ?>
    <div class="uniform-errors">
        <?php foreach ($form->errors() as $key => $error): ?>
            <div class="uniform-errors__item">
                <?php echo implode('<br>', $error) ?>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
