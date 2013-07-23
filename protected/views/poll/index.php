<div class='clear'></div>
<?php
$this->widget('zii.widgets.CBreadcrumbs', array(
    'links' => array(
        'Home' => array('home/index'),
    ),
    'links' => array(
        'Poll' => array('poll/index'),
    ),
));
?>
<?php
    $this->widget('bootstrap.widgets.TbAlert');
?>
<div class='row'>
    <div class='span6'>
        <h2><?php echo $title; ?></h2>
    </div>
    <div class='span2'>
        <br/>
        <?php
        echo CHtml::button('Create Poll', array(
            'submit' => array('poll/create'),
            'class' => 'btn btn-success'
        ));        
        ?>
    </div>
    <div class='span2'>
        <br/>
        <?php
        echo CHtml::button('Show Search ', array(
            'submit' => array(),
            'class' => 'btn btn-primary'
        ));
        ?>
    </div>
</div>
<hr style="color: #808080">
<?php
foreach ($polls as $poll) {
    echo "<div class='row'>";
    $this->renderPartial('_view', array('poll' => $poll));
    echo "</div>";
}
?>