<div class="container">
    <!-- Bundle information -->
    <div class="view_panel">
        <h1 class="view_header">
            Bundle info
        </h1>
        <div class="view_content">
            <?php if ($error): ?>
                <div class="alert alert-danger fade show" role="alert">
                    <button 
                        class="close" 
                        data-dismiss="alert" 
                        aria-label="close"
                    >
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
                        Bundle name*
                    </label>
                    <input 
                        id="name" 
                        type="text" 
                        name="name" 
                        placeholder="Name" 
                        class="form-control" 
                        value="<?php echo $bundle->getName(); ?>" 
                        required 
                    />
                </div>
                
                <div class="form-group">
                    <label for="price">
                        Price*
                    </label>
                    <input 
                        id="price" 
                        name="price" 
                        type="text" 
                        placeholder="Price" 
                        class="form-control price" 
                        value="<?php echo number_format($bundle->getPrice(), 2); ?>" 
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
                        <?php echo $bundle->getDescription(); ?>
                    </textarea>
                </div>
                
                <div class="form-group">
                    <label for="logo">
                        Logo
                    </label>
                    <img	class="manager-logo"
                            src="<?php echo empty($bundle->getLogo()) ? 
                                BASE_URL."../src/main/webapp/images/default/noImage.png" : 
                                BASE_URL."../src/main/webapp/images/logos/bundles/".$bundle->getLogo(); ?>"
                    />
                    <a 
                        href="<?php echo BASE_URL."bundles/deleteLogo/".$bundle->getId(); ?>" 
                        class="btn_theme btn_full btn_theme_danger"
                    >
                        Remove logo
                    </a>
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
                        value="Update" 
                        class="form-control btn_theme btn_theme_full" 
                    />
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bundle courses -->
    <div class="view_panel">
        <h1 class="view_header">
            Bundle courses
        </h1>
        <div class="view_content">
            <table class="table table-hover table-stripped text_centered">
                <thead>
                    <tr>
                        <th></th>
                        <th>
                            Name
                        </th>
                        <th>
                            Description
                        </th>
                        <th>
                            Students
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $course): ?>
                        <tr>
                            <?php if (empty($course->getLogo())): ?>
                                <td class="manager-table-logo">
                                    <img 
                                        class="img img-responsive" 
                                        src="<?php echo BASE_URL."../src/main/webapp/images/default/noImage.png"; ?>" 
                                    />
                                </td>
                            <?php else: ?>
                                <td class="manager-table-logo">
                                    <img 
                                        class="img img-responsive" 
                                        src="<?php echo BASE_URL."../src/main/webapp/images/logos/courses/".$course->getLogo(); ?>" 
                                    />
                                </td>
                            <?php endif; ?>
                            <td>
                                <?php echo $course->getName(); ?>
                            </td>
                            <td>
                                <?php echo $course->getDescription(); ?>
                            </td>
                            <td>
                                <?php echo $course->getTotalStudents(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button 
                class="btn_theme" 
                onclick="show_updateBundle(<?php echo $bundle->getId(); ?>)"
            >
                Include courses
            </button>
        </div>
    </div>
    
    <!-- Modals -->
    <?php $this->loadView("bundlesManager/IncludeCoursesModal", array('id_bundle' => $bundle->getId())); ?>
</div>