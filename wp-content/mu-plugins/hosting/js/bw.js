jQuery(document).ready(function(){
	jQuery('#bwp_updateInfo').on('click', function(){
		jQuery('.email-verify-text').show('slow');
		jQuery.post('admin-ajax.php', {action: 'email_verify_code'}, function(result){
			if(result === "success"){
				jQuery('.email-verify-text').hide('slow');
				jQuery('.email-verify').show('slow');	
			}
		});
	});
	jQuery('.verifycode-btn').on('click', function(){
		jQuery.post('admin-ajax.php', {action:'verify_email', verification_code: jQuery('#email-verify').val()}, function(result){
			if(result === 'verified'){
				jQuery('#bw_updateAddr').removeAttr('disabled');
				
				jQuery('.bwinfo-input').removeAttr('readonly');
			}else if('invalid'){
				alert('please cehck your code again');
			}else{
				alert('please add verification code sent to your mail')
			}
		});
	});
	jQuery('#bw_updateAddr').on('click', function(){
		var data = {
			'action' : 'update_addr',
			'first_name' : jQuery('#first_name').val(),
			'last_name' : jQuery('#last_name').val(),
			'phone' : jQuery('#phone').val(),
			'address' : jQuery('#address').val(),
			'city' : jQuery('#city').val(),
			'state' : jQuery('#state').val(),
			'zip' : jQuery('#zip').val(),
			'country' : jQuery('#country').val(),
		};
		jQuery.post('admin-ajax.php', data, function(result){
			if(result === "updated"){
				alert('Info Updated');
			}else{
				alert('Some error occured, please try again later');
			}
		});
	});
});