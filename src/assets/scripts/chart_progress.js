/**
 * Responsible for generating progress chart.
 */
class ChartProgress
{
	//-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
	constructor()
	{
		this.week = this._last7Days().reverse()
		this.dataWeek = new Map()
		
		for (let i=0; i<7; i++) {
			this.dataWeek.set(this.week[i], 0)
		}	
	}
	
	
	//-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
	/**
	 * Generates progress chart.
	 */
	render()
	{
		this._getWeekProgress()
		this._progressChart()
	}
	
	/**
	 * Gets total number of classes that the student has watched in the last 7
     * days. The result will be saved in @{link #dataWeek}.
	 */
	_getWeekProgress()
	{
		$.ajax({
			type:'POST',
			url:BASE_URL+"home/weekly_progress",
			datatype:'json',
			async:false,
			success:(json) => {
				let data = JSON.parse(json)
				
				for (let i in data) {
					if (this.dataWeek.has(data[i].date)) {
						this.dataWeek.set(data[i].date, parseInt(data[i].total_classes_watched))
					}
				}
			} 
		})
	}
	
	/**
	 * Gets a date and convert it to the following format: YYYY/MM/DD
	 * 
	 * @param		Date Date to be converted
	 *
	 * @return		String Data in the following format: YYYY/MM/DD
	 */
	_formatDate(date)
	{
	    let dd = date.getDate();
	    let mm = date.getMonth()+1;
	    const yyyy = date.getFullYear();

	    if(dd<10) {dd='0'+dd}
	    if(mm<10) {mm='0'+mm}
	    
		//date = mm+'/'+dd+'/'+yyyy;
		date = yyyy+'/'+mm+'/'+dd;
	    
		return date
	 }
	
	/**
	 * Gets dates between today and 7 days ago.
	 * 
	 * @return		array Dates between today and 7 days ago in the following
	 * format: YYYY/MM/DD
	 */
	_last7Days () 
	{
	    let result = []
	
		for (let i=0; i<7; i++) {  
			let d = new Date()      
	        d.setDate(d.getDate() - i)
	        result.push(this._formatDate(d))
	    }
	
	    return result
	}
	
	/**
	 * Creates progress chart.
	 */
	_progressChart()
	{
		const chart = document.getElementById('chart_progress').getContext('2d')
		
		return new Chart(chart, {
		    type: 'line',
		    data: {
		        labels: [...this.week],
		        datasets: [{
		            label: 'Watched classes',
		            data: [
						{x: this.week[0], y:this.dataWeek.get(this.week[0])},
						{x: this.week[1], y:this.dataWeek.get(this.week[1])},
						{x: this.week[2], y:this.dataWeek.get(this.week[2])},
						{x: this.week[3], y:this.dataWeek.get(this.week[3])},
						{x: this.week[4], y:this.dataWeek.get(this.week[4])},
						{x: this.week[5], y:this.dataWeek.get(this.week[5])},
						{x: this.week[6], y:this.dataWeek.get(this.week[6])}
					],
					fill:false,
		            backgroundColor:'#7d4689',
		            borderColor: '#7d4689',
		            borderWidth: 1
		        }]
		    },
		    options: {
		        responsive:true,
				scales: {
			        yAxes: [{
			            ticks: {
			                min: 0,
			                stepSize: 1
			            }
			        }]
			    }
		    }
		})
	}
}

const chartProgress = new ChartProgress()
chartProgress.render()