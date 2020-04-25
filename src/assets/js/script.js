$(function(){
	updateScreen()
	
	window.onresize = function() {
		updateScreen()
	}
	
	$(".question").click(function() {
		var id_quest = $(".questions").attr("data-quest")
		var selectedQuestion = $(this).attr("data-index")
		
		$.ajax({
			type:"POST",
			url:BASE_URL+"ajax/quests",
			data:{id_quest:id_quest},
			success:function(ans) {
				if (ans == selectedQuestion) {
					alert("Correct!")
				} else {
					alert("Incorrect!")
				}
				
				$(".question").each(function(index) {
					$(this).addClass(index+1 == ans ? "question_correct" : "question_wrong")
					
					if (index+1 == selectedQuestion) {
						$(this).addClass("question_selected")
					}
				})
				
				$(".question").unbind()
			}
		})
	})
})

function updateScreen()
{
	var offset = $(".course_left").offset().top
	var windowHeight = $(document.body).height()
	var newHeight = windowHeight - offset
	
	$(".course_left").css("height", newHeight+"px")
	$(".course_right").css("height", newHeight+"px")
	
	var ratio = 1920/1080
	var videoWidth = $("#class_video").width()
	var videoHeight = videoWidth/ratio
	
	$("#class_video").css("height", videoHeight+"px")
}

function markAsWatched(id_class)
{
	// ADICIONAR OPÇÃO DE REMOVER AULA VISTA
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/mark_class_watched",
		data:{id_class:id_class}
	})
	
	$(".content_info").hide().append(`<small class="class_watched">Watched</small>`).fadeIn("fast")
	$(".btn_mark_watch").attr("onclick", "removeWatched("+id_class+")")
}

function removeWatched(id_class)
{
	// ADICIONAR OPÇÃO DE REMOVER AULA VISTA
	$.ajax({
		type:"POST",
		url:BASE_URL+"ajax/remove_watched_class",
		data:{id_class:id_class}
	})
	
	$(".class_watched").fadeOut("fast")
	$(".btn_mark_watch").attr("onclick", "markAsWatched("+id_class+")")
}