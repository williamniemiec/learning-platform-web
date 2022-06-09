<div class="container">
    <div class="view_panel">
        <h1 class="view_header">
            New course
        </h1>
        <div class="view_content">
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
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">
                        Course name*
                    </label>
                    <input 
                        id="name" 
                        type="text" 
                        name="name" 
                        placeholder="Name" 
                        class="form-control"
                        required 
                    />
                </div>
                
                <div class="form-group">
                    <label for="description">
                        Description name
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control"
                    >
                    </textarea>
                </div>
                
                <div class="form-group">
                    <label for="logo">
                        Logo
                    </label>
                    <input 
                        id="logo" 
                        name="logo" 
                        type="file" 
                        accept=".jpeg,.png,.jpg" 
                        class="form-control" 
                    />
                </div>
                
                <div class="form-group">
                    <input 
                        type="submit" 
                        value="Create" 
                        class="btn_theme btn_theme_full form-control" 
                    />
                </div>
            </form>
        </div>
    </div>
</div>