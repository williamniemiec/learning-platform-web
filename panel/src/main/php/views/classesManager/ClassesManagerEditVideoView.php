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
                        <span aria-hidden="true">
                            &times;
                        </span>
                    </button>
                    <h4 class="alert-heading">
                        Error
                    </h4>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <!-- Class information -->
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" value="v" />
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
                <input type="hidden" name="type" value="v" />
                <div class="form-group">
                    <label for="title">
                        Title*
                    </label>
                    <input 
                        id="title" 
                        type="text" 
                        name="title" 
                        placeholder="Title" 
                        class="form-control" 
                        value="<?php echo $class->getTitle(); ?>" 
                        required 
                    />
                </div>
                <div class="form-group">
                    <label for="description">
                        Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        placeholder="Description" 
                        class="form-control"
                    >
                        <?php echo $class->getDescription(); ?>
                    </textarea>
                </div>
                <div class="form-group">
                    <label for="videoID">
                        VideoID*
                    </label>
                    <input 
                        id="videoID" 
                        type="text" 
                        name="videoID" 
                        placeholder="VideoID (YouTube URL - content to the right of 'v=')" 
                        class="form-control" 
                        pattern="[0-9A-z]{11}" 
                        value="<?php echo $class->getVideoID(); ?>" 
                        required 
                    />
                </div>
                <div class="form-group">
                    <label for="length">
                        Length
                    </label>
                    <input 
                        id="length" 
                        name="length" 
                        type="number" 
                        placeholder="Video length (integer)" 
                        value="<?php echo $class->getLength(); ?>" 
                        class="form-control" 
                    />
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