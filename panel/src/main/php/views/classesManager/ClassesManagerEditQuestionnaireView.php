<div class="container">
    <div class="view_panel">
        <h1 class="view_header">
            Class edit
        </h1>
        <div class="view_content">
            <!-- Error message -->
            <?php if ($error): ?>
                <div class="alert alert-danger fade show" role="alert">
                    <button class="close" data-dismiss="alert" aria-label="close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="alert-heading">
                        Error
                    </h4>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <!-- Class information -->
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="id_module">
                        Module (class order will be the highets class order in use + 1)
                    </label>
                    <select 
                        name="id_module" 
                        class="form-control" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="To change class order go to modules manager"
                    >
                        <?php foreach ($modules as $k => $module): ?>
                            <option 
                                value='<?php echo $module->getId(); ?>' 
                                <?php echo $module->getId() == $class->getModule()->getId() ? "selected" : "" ?>
                            >
                                <?php echo $module->getName(); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="type" value="q" />
                <div class="form-group">
                    <label for="question">
                        Question*
                    </label>
                    <input 
                        id="name" 
                        type="text" 
                        name="question" 
                        placeholder="Question" 
                        value="<?php echo $class->getQuestion(); ?>" 
                        class="form-control" 
                        required 
                    />
                </div>
                
                <div class="form-group">
                    <label for="description">
                        Question 1
                    </label>
                    <input 
                        id="q1" 
                        type="text" 
                        name="q1" 
                        placeholder="Question 1" 
                        class="form-control" 
                        value="<?php echo $class->getQ1(); ?>" 
                        required 
                    />
                </div>
                
                <div class="form-group">
                    <label for="description">
                        Question 2
                    </label>
                    <input 
                        id="q2" 
                        type="text" 
                        name="q2" 
                        placeholder="Question 2" 
                        class="form-control" 
                        value="<?php echo $class->getQ2(); ?>" 
                        required 
                    />
                </div>
                
                <div class="form-group">
                    <label for="description">
                        Question 3
                    </label>
                    <input 
                        id="q3" 
                        type="text" 
                        name="q3" 
                        placeholder="Question 3" 
                        class="form-control" 
                        value="<?php echo $class->getQ3(); ?>" 
                        required 
                    />
                </div>
                
                <div class="form-group">
                    <label for="description">
                        Question 4
                    </label>
                    <input 
                        id="q4" 
                        type="text" 
                        name="q4" 
                        placeholder="Question 4" 
                        class="form-control" 
                        value="<?php echo $class->getQ4(); ?>" 
                        required 
                    />
                </div>
                <div class="form-group">
                    <label for="answer">
                        Answer*
                    </label>
                    <select id="answer" name="answer" class="form-control">
                        <option 
                            value='1' 
                            <?php echo $class->getAnswer() == 1? "selected" : "" ?>
                        >
                            1
                        </option>
                        <option 
                            value='2' 
                            <?php echo $class->getAnswer() == 2? "selected" : "" ?>
                        >
                            2
                        </option>
                        <option 
                            value='3' 
                            <?php echo $class->getAnswer() == 3? "selected" : "" ?>
                        >
                            3
                        </option>
                        <option 
                            value='4' 
                            <?php echo $class->getAnswer() == 4? "selected" : "" ?>
                        >
                            4
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <input 
                        type="submit" 
                        value="Update" 
                        class="form-control btn_theme btn_theme_full" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>