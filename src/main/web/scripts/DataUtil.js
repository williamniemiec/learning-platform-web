/**
 * Gets a date and convert it to the following format: YYYY/MM/DD
 * 
 * @param		Date Date to be converted
 *
 * @return		String Data in the following format: YYYY/MM/DD
 */
function formatDate(date, withTime = true, formatter = globalFormatter)
{
	let formattedDate = ''
    let dd = date.getDate()
    let mm = date.getMonth()+1
    const yyyy = date.getFullYear()

    if(dd<10) {dd='0'+dd}
    if(mm<10) {mm='0'+mm}
    
	formattedDate = formatter(yyyy, mm, dd)
	
	if (withTime)
		formattedDate += ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()
    
	return formattedDate
}

/**
 * Fomrmats date in the following format: YYYY/MM/DD.
 *
 * @param		string yyyy Year
 * @param		string mm Month
 * @param		string dd Day
 *
 * @return		string Date
 */
export function globalFormatter(yyyy, mm, dd)
{
	return yyyy + '/' + mm + '/' + dd
}

/**
 * Fomrmats date in the following format: MM/DD/YYYY.
 *
 * @param		string yyyy Year
 * @param		string mm Month
 * @param		string dd Day
 *
 * @return		string Date
 */
export function americanFormatter(yyyy, mm, dd)
{
	return mm + '/' + dd + '/' + yyyy
}

export default formatDate