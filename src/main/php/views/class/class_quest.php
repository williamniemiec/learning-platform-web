<div class="class_content">
    <h3>
        <?php echo $class->getQuestion(); ?>
    </h3>
    <div 
        class="questions" 
        data-id-module="<?php echo $class->getModuleId(); ?>" 
        data-class-order="<?php echo $class->getClassOrder(); ?>"
    >
        <div class="question" data-index="1">
            <?php echo $class->getQ1(); ?>
        </div>
        <div class="question" data-index="2">
            <?php echo $class->getQ2(); ?>
        </div>
        <div class="question" data-index="3">
            <?php echo $class->getQ3(); ?>
        </div>
        <div class="question" data-index="4">
            <?php echo $class->getQ4(); ?>
        </div>
    </div>
</div>