$(document).ready(function() {
var filePath = "http://socialpancake.net/";
	//toggle mobile menu
	$('.mobile-menu-icon').on('click', function() {
		if ($('.main-content').css("display") != "none") {
			var scroll_t = $(window).scrollTop();
			$('.home-top').removeClass('home-top');
			$('.mobile-menu').fadeIn();
			$('#search-text-input').focus();
			$('.menu-icon .st1').css("fill", "#fff");
			$('.main-content').hide();
			$('#scrollPos').val(scroll_t);
			$(window).scrollTop(0);
		} else {
			var scroll_t = $('#scrollPos').val();
			$('.mobile-menu').hide();
			$('.menu-icon .st1').css("fill", "#04402d");
			$('.main-content').fadeIn();
			$(window).scrollTop(scroll_t);
		}
	});


	$('.nav-profile-pic').on('click', function() {
		if ($('.user-menu-outer').css("display") == "none") {
			$('.user-menu-outer').show();
			$('.nav-profile-pic').css("transform", "scale(.94)");
		} else {
			$('.user-menu-outer').hide();
			$('.nav-profile-pic').css("transform", "scale(1)");
		}
	});

	$('.close').on('click', function () {
		if ($('#postEdit') !== "") {
			$('.site-post textarea').val("");
			$('#file-img img').attr('src', "");
			$('#postEdit').val("");
			$('#youtube-video-edit .youtube-video').remove();
		}
	});

	$(".home-top").click(function(e) {
			e.preventDefault();
	  	$("html, body").animate({ scrollTop: 0 }, 400);
		return false;
	});

	$(".image-upload").click(function() {
			$('#fileToUpload').trigger('click');
	});

	$('#post_form').on('hidden.bs.modal', function () {
		if ($('#postEdit').val()) {
			$('#postEdit').val('');
			$('#fileToUpload').val('');
			$('.site-post textarea').val('');
			$('#youtube-video-edit .youtube-video').remove();

			$('#file-img img').attr('src', '');
			$('#file-img').css("display", "none");
		}
	});

	//ajax request that submits loggedInUser's posts to visited profile
	$('#submit_profile_post').click(function(e) {
		var form = $('.site-post');
		var formData = $(form).serialize();
		e.preventDefault();

		$.ajax({
			type: 'POST',
			url: "inc/ajax/ajax-submit-profile-posts.php",
			data: formData
		})
		.done(function(response) {
			$("#profile_post_form").modal('hide');
			location.reload();
		})
		.fail(function(data) {
			alert('Failed to submit post!');
		});

	});

	$(document).keydown(function(e){
      if( e.which === 90 && e.ctrlKey && e.shiftKey ){
				var scroll_t = $(window).scrollTop();
			  $('.home-top').removeClass('home-top');
			  $('.mobile-menu').fadeIn();
				$('#search-text-input').focus();
			  $('.menu-icon .st1').css("fill", "#fff");
			  $('.main-content').hide();
			  $('#scrollPos').val(scroll_t);
			  $(window).scrollTop(0);
      }
	});

	//ajax request that submits loggedInUser's posts to visited profile
	$('.submit_friend_post').click(function(e) {
		var form = $('.site-post');

		var formData = $(form).serialize();
		// form.append('media', file);
		e.preventDefault();

		$.ajax({
			type: 'POST',
			url: "inc/ajax/ajax-submit-posts.php",
			data: new FormData($('.site-post')[0]),
		    cache: false,
		    contentType: false,
			processData: false
		})
		.done(function(response) {
			$(".friend_post_form").modal('hide');
			location.reload();
		})
		.fail(function(data) {
			alert('Failed to submit post!');
		});

	});

	//newsfeed pocketscroll effect
	var  nf = $(".newsfeed-form");
	var nfs = "newsfeed-form-scrolled";
	var  ps = $(".friend-pocket-scroll-top");
	var pss = "friend-pocket-scroll-top-scrolled";

	$(window).scroll(function() {
	if( $(this).scrollTop() > 0) {
	  nf.addClass(nfs);
	  ps.addClass(pss);
	 } else {
	  nf.removeClass(nfs);
	  ps.removeClass(pss);
	 }
	});

	//textarea auto expand
	// Applied globally on all textareas with the "autoExpand" class
	$(document)
	.one('focus.autoExpand', 'textarea.autoExpand', function(){
	    var savedValue = this.value;
	    this.value = '';
	    this.baseScrollHeight = this.scrollHeight;
	    this.value = savedValue;
	})
	.on('input.autoExpand', 'textarea.autoExpand', function(){
	    var minRows = this.getAttribute('data-min-rows')|0, rows;
	    this.rows = minRows;
	    rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / 17);
	    this.rows = minRows + rows;
	});
	//show profile-settings
	$( "#nav-profile-pic" ).on( "click", function() {
		if ($('.user-settings').css('left') == '-300px')
	 		$('.user-settings').css('left', '60px');
	 	else if ($('.user-settings').css('left') == '60px') {
	 		$('.user-settings').css('left', '-300px');
	 	}
	});
	//hide profile settings on body click
	$('user-settings').on('click', function(e) {
	    e.stopPropagation();
	});

	$('#post_form, #profile_post_form, .friend-post').on('shown.bs.modal', function () {
	    $('.form-control').focus();
	});

	$(document).on('click', function (e) {
		if ($('.user-settings').css('left') == '60px') {
	 		$('.user-settings').css('left', '-300px');
	 	}
	});

});

//hides search results and notifications on click-away
$(document).click(function(e){

	if(e.target.class != "search-results" && e.target.id != "search-text-input") {
		$(".search-results").html("");
	}

	// if(e.target.class != "user-menu") {
	// 		$(".user-menu").hide();
	// }

	// if(e.target.className != "user-menu" && e.target.className != "nav-profile-pic") {
	// 	alert("hello");
	// 	$(".user-menu").hide();
	// }

	if ($('.mobile-menu').css("display") != "none") {

		if(e.target.className == "doc-body" || e.target.className == "wrapper" || e.target.className == "Navigation" && e.target.className != "nav-profile-pic") {

				var scroll_t = $('#scrollPos').val();
				$('.mobile-menu').hide();
				$('.menu-icon .st1').css("fill", "#04402d");
				$('.main-content').fadeIn();
				$(window).scrollTop(scroll_t);
				if ($('.user-menu').css("display") !== "none") {
					$(".user-menu").hide();
				}
		}

	}

});

function getLiveSearchUsers(value, user) {

	$.post(filePath + "inc/ajax/ajax-search.php", {query:value, loggedInUser: loggedInUser}, function(data) {
		$('.search-results').html(data);
		$('.search-results-footer').html("<a href=" + filePath + "'search.php?query=" + value + "'>See All Results</a>");
	});

}

function getUsers(value, user) {
	$.post(filePath + "inc/ajax/ajax-friend-search.php", {query:value, loggedInUser: user}, function(data) {
		$(".results").html(data);
	});
}
