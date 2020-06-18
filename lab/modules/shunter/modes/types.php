<?

$type = "";

if(in_array(USER_GROUP, $privileged)){
	$where = $mySQL->parse(
		"WHERE type IN ({arr})",
		preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['showed types'], -1, PREG_SPLIT_NO_EMPTY)
	);
}else $where = $mySQL->parse(
	"WHERE type IN ({arr}) AND (CommunityID IS NULL OR CommunityID = {int})",
	preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['showed types'], -1, PREG_SPLIT_NO_EMPTY),
	COMMUNITY_ID
);

$stream = $mySQL->get("
	SELECT * FROM gb_task_timing 
	CROSS JOIN gb_task_shunter USING(TaskID)
	LEFT JOIN gb_community USING(CommunityID)
	{prp}
	ORDER BY type, rank DESC", $where
)?>
<section>
	<aside>
<?foreach($stream as $task): if($task['type']!=$type):$type=$task['type']?>
	</aside>
</section>
<section class="task-feed">
	<div class="h-bar light-txt" data-translate="textContent"><?=$type?> ❯</div>
	<aside data-type="<?=$task['type']?>" ondragover="event.preventDefault()" ondrop="drop(event)">
<?endif?>
		<div id="task-<?=$task['TaskID']?>" data-id="<?=$task['TaskID']?>" class="slot" draggable="true" ondragstart="drag(event)">
			<div class="card snippet <?=$type?>">
				<div class="preview"><img src="<?=$task['image']?>" alt="&#xe94a;"></div>
				<div class="header"><span><?=$task['header']?></span></div>
				<div class="task"><?=$task['task']?></div>
				<div class="options">
					<span class="created red-txt"><?=date("d M, H:i", $task['created'])?></span>
					<span class="performer"><?=$task['Name']?></span>
				</div>
				<?if($task['link']):?><a href="<?=$task['link']?>" target="_blank" title="follow">❯</a><?endif?>
			</div>
		</div>
<?endforeach?>
	</aside>
</section>
<script>
(function(desk){
	desk.querySelectorAll(".slot").forEach(function(slot){
		slot.ondragend=function(event){
			slot.classList.toggle("grabbing", false);
		}
	});
})(document.currentScript.parentNode)

function drag(event){
	event.currentTarget.classList.toggle("grabbing", true);
	event.dataTransfer.effectAllowed = "move";
	event.dataTransfer.setData("text", event.target.id);
}
function drop(event){
	event.preventDefault();
	var data = event.dataTransfer.getData("text"),
		card = document.getElementById(data),
		section = event.currentTarget;
	section.insertToBegin(card);
	XHR.push({
		addressee:"/shunter/actions/change-type/"+card.dataset.id,
		body:JSON.encode({
			type:section.dataset.type
		})
	});
}
</script>