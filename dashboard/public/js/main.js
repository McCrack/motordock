/* Table rows ******************************************************/

function addRow(row){
	var newRow = row.cloneNode(true);
		newRow.querySelectorAll("td").forEach(function(cell){
		cell.innerHTML = "";
		cell.setAttribute('contenteditable', "true");
	});
	row.insertAdjacentElement("afterEnd", newRow);
}
function deleteRow(row, onDelete){
	row.parentNode.removeChild(row);
	if(onDelete) onDelete();
}