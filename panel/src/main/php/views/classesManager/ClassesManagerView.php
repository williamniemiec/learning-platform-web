<?php
use panel\domain\Video;
?>
<div class="container">
    <div class="view_panel">
        <h1 class="view_header">
            Classes manager
        </h1>
        <div class="view_content">
            <?php if (count($classes) == 0): ?>
                <h2>
                    There are no registered classes
                </h2>
            <?php else: ?>
                <a class="btn_theme" href="<?php BASE_URL; ?>classes/new">
                    New
                </a>
                <table class="table table-hover table-stripped text_centered">
                    <thead>
                        <tr>
                            <th>
                                Name
                            </th>
                            <th>
                                Type
                            </th>
                            <th>
                                Module
                            </th>
                            <th>
                                Order
                            </th>
                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($classes as $class): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BASE_URL."classes/edit/".$class->getModuleId()->getId()."/".$class->getClassOrder(); ?>">
                                        <?php echo $class instanceof Video ? $class->getTitle() : $class->getQuestion(); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo $class instanceof Video ? "Video" : "Questionnaire"; ?>
                                </td>
                                <td>
                                    <?php echo $class->getModuleId()->getName(); ?>
                                </td>
                                <td>
                                    <?php echo $class->getClassOrder(); ?>
                                </td>
                                <td class="actions">
                                    <a 
                                        class="btn_theme" 
                                        href="<?php echo BASE_URL."classes/edit/".$class->getModuleId()->getId()."/".$class->getClassOrder(); ?>"
                                    >
                                        Edit
                                    </a>
                                    <a 
                                        class="btn_theme btn_theme_danger" 
                                        href="<?php echo BASE_URL."classes/delete/".$class->getModuleId()->getId()."/".$class->getClassOrder(); ?>"
                                    >
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item <?php echo ($currentIndex - 1 <= 0) ? "disabled" : ""; ?>">
                        <a 
                            href="<?php echo BASE_URL."classes?index=".($currentIndex - 1); ?>" 
                            class="page-link"
                        >
                            Before
                        </a>
                    </li>
                    <?php for ($i=1; $i<=$totalPages; $i++): ?>
                        <li 
                            class="page-item <?php echo $i == $currentIndex ? "active" : "" ?>" 
                            data-index="<?php echo $i; ?>"
                        >
                            <a 
                                href="<?php echo BASE_URL."classes?index=".$i; ?>" 
                                class="page-link"
                            >
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($currentIndex + 1 > $totalPages) ? "disabled" : ""; ?>">
                        <a 
                            href="<?php echo BASE_URL."classes?index=".($currentIndex + 1); ?>" 
                            class="page-link"
                        >
                            After
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>