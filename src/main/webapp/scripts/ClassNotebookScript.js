import NotebookNavigator from './NotebookNavigator.js'


/**
 * Responsible for handling notebook pagination.
 *
 * @param		String action 'af' for go forward, 'bef' to move backward and 
 * 'go' to go to a specific page
 * @param		int goto Page to go
 * @param		int id_module Module to which the class belongs
 * @param		int class_order Class order in module
 */
function navigate(action, goto = -1, id_module, class_order)
{
	let nav = new NotebookNavigator(2, "notebook/get_all_from_class", {id_module, class_order})
	nav.navigate(action, goto)
}


// Exports
window.navigate = navigate