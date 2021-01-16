<?php foreach($this->_flash->getInfo() as $info): ?>
<div class="updated fade is-dismissible">
    <p><?php echo $info; ?></p>
</div>
<?php endforeach; ?>

<?php foreach($this->_flash->getError() as $error): ?>
<div class="error">
    <p><?php echo $error; ?></p>
</div>
<?php endforeach; ?>

<?php $this->_flash->dispose() ?>
<?php $this->_flash->save() ?>