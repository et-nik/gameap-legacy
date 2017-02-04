$('#dbdrivers').change(
	function(){
		setHiddenState();
	}
);

$('#pdodrivers').change(
	function(){
		setHiddenState();
	}
);

function setHiddenState(){
	if($('#dbdrivers').val() == 'pdo'){
		$('#pdodrivers_row').attr('hidden',false);
		
		if($('#pdodrivers').val() == 'sqlite'){
			$('.noForSqlite').attr('hidden',true);
		}else
			$('.noForSqlite').attr('hidden',false);
	}else{
		$('#pdodrivers_row').attr('hidden',true);
		$('.noForSqlite').attr('hidden',false);
	}
}

$(document).ready(
	function(){
		setHiddenState();
	}
);