# Building Blocks

Here are some form building blocks for quick reference. Take a look at the [extended example](extended) for a working form with different types of form fields.

## Text

```html+php
<label>My field</label>
<input<?php if ($form->error('myfield')): ?> class="error"<?php endif; ?> name="myfield" type="text" value="<?php echo $form->old('myfield') ?>">
```

## Textarea

```html+php
<label>My field</label>
<textarea<?php if ($form->error('myfield')): ?> class="error"<?php endif; ?> name="myfield"><?php echo $form->old('myfield') ?></textarea>
```

## Checkbox

```html+php
<label>
    <?php $value = $form->old('myfield') ?>
    <input type="checkbox" name="myfield" value="true"<?php e(!$value || $value=='true', ' checked')?>/> Confirm
</label>
```

## Radio

```html+php
<?php $value = $form->old('myfield') ?>
<label>
    <input type="radio" name="myfield" value="yes"<?php e(!$value || $value=='yes', ' checked')?>/> Yes
</label>
<label>
    <input type="radio" name="myfield" value="no"<?php e($value=='no', ' checked')?>/> No
</label>
```

## Select

```html+php
<label>Booth</label>
<?php $value = $form->old('myfield') ?>
<select name="myfield">
    <option value="option1"<?php e(!$value || $value=='option1', ' selected')?>>First</option>
    <option value="option2"<?php e($value=='option2', ' selected')?>>Second</option>
    <option value="option3"<?php e($value=='option3', ' selected')?>>Third</option>
</select>
```
