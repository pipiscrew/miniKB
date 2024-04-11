<?php @session_start(); ?>
<!DOCTYPE html>
<html>
<!--
https://getbootstrap.com/docs/3.3/css/#grid
https://blog.jquery.com/2013/02/04/jquery-1-9-1-released/

#Bootstrap Tree View
	https://github.com/jonmiles/bootstrap-treeview
	http://jonmiles.github.io/bootstrap-treeview/

#markdown
	wrapper for online - https://github.com/benweet/stackedit  --  https://benweet.github.io/stackedit.js/
	https://github.com/nhn/tui.editor
	https://github.com/showdownjs/showdown
	https://github.com/markedjs/marked || https://marked.js.org/demo/ || https://marked.js.org/#installation
	https://github.com/markdown-it/markdown-it
	* https://github.com/jonschlinkert/remarkable

# summernote
https://github.com/summernote/summernote
https://summernote.org/plugins
https://summernote.org/deep-dive/#custom-styles (add extra default style)

https://github.com/JefMari/awesome-wysiwyg-editors

plugs - https://github.com/summernote/awesome-summernote
	http://www.alpacajs.org/docs/fields/summernote.html
	https://github.com/DiemenDesign/summernote-save-button
	https://github.com/ilyasozkurt/summernote-sticky-toolbar
	https://github.com/tylerecouture/summernote-add-text-tags
	https://github.com/DiemenDesign/summernote-save-button
	https://github.com/DiemenDesign/summernote-cleaner
	https://github.com/wubin1989/summernote-ace-plugin
	https://github.com/semplon/summernote-ext-codewrapper (already done in summernote with default styles)
-->

  <head>
    <title>miniKB</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap-treeview.min.css" rel="stylesheet">
	<link href="assets/css/summernote.min.css" rel="stylesheet">

	<style type="text/css">
			.modal-backdrop {
				opacity: 0.7;
				filter: alpha(opacity=70);
				background: #fff;
				z-index: 2;
			}

			div.loading {
				position: fixed;
				margin: auto;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				width: 200px;
				height: 30px;
				z-index: 3;
			}
			.disabled {
				opacity: 0.5;
				pointer-events: none;
			}
		</style>

  </head>
  <body style="margin:15px">
  <nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">miniKB</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" id="txtSearch" class="form-control" placeholder="to search press enter">
        </div>
<?php if (isset($_SESSION['id'])) { ?>
		<button type="button" id='btnNew' onClick="NewArticle()" style="margin-left:20px" class="btn btn-success navbar-btn">new</button>
        <button type="button" id='btnSave' onClick="SaveArticle()" style="margin-left:5px" class="btn btn-primary navbar-btn">save</button>
		<button type="button" id='btnDelete' onClick="DeleteArticle()" style="margin-left:5px" class="btn btn-danger navbar-btn">delete</button>
		<button type="button" style="margin-left:5px" onClick="MoveArticle()" class="btn btn-primary navbar-btn">move</button>
<?php } ?>
      </form>
      <ul class="nav navbar-nav navbar-right">
<?php
	  	if (isset($_SESSION['id'])) 
		  echo '<li><a href="login.php?logout=1">logout</a></li>';
		else 
		  echo '<li><a href="login.php">login</a></li>';
?>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
  
	<div class="row">
	  <div class="col-md-2">
	  	<div id="toc"></div>
	  </div>
	  <div class="col-md-10"> 
			<div class='form-group'>
				<label for="txtArticle">Article :</label> <span id="articlePath" class="label label-success"></span>
				<input type="email" class="form-control" id="txtArticle" placeholder="article name">
			</div>
	  		<textarea name="text" class="summernote" id="contents" title="Contents"></textarea>
		</div>
	</div>
	
	<!-- MOVE modal -->
	<div class="modal fade bs-example-modal-sm" id="moveNodeModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Move Node and its children</h4> 
				</div>
				<div class="modal-body">
					<label for="parentName">search parent name :</label>
					<input name="parentName" class="form-control" maxlength="50" type="text" id="parentName" placeholder="where you want to move it">
					<button type="button" onClick="MoveSearchButton()" class="btn btn-primary btn-sm">search</button> <br/>
					<label for="parentNameSelector">available parents :</label>
					<select name="parentNameSelector" class="form-control" id="parentNameSelector">
					</select>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">cancel</button>
					<button type="button" onClick="MoveSaveButton()" class="btn btn-primary">move</button>
				</div>
			</div>
		</div>
	</div>

    <script src="assets/jquery-3.6.0.min.js"></script>
	<script src="assets/bootstrap.min.js"></script>
  	<script src="assets/bootstrap-treeview.js"></script>
	<script src="assets/summernote.min.js"></script>
	<script src="assets/md5.min.js"></script>
	
	<script>
		//indicator 
		var loading = $('<div class="modal-backdrop"></div><div class="progress progress-striped active loading"><div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">');
		
		function indicator(val){
			if (val)
				loading.appendTo(document.body);	
			else 
				loading.remove();
		}

		$(function() {
			LoadTOC();

			$('.summernote').summernote({
				height:$(window).height(),
 			});

			$('#txtSearch').on('keypress', function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();

					if ($('#toc').hasClass('disabled')) //if in state /add new/
						return;

					let s = $('#txtSearch').val().trim();

					if (s) //search
						LoadTOC(true);
					else //restore KB
						LoadTOC();
				}
			});

			$('#moveNodeModal').on('hidden.bs.modal', function() { //modal close event - clear items
                    $('#parentName').val('');
					$('#parentNameSelector').html('');
            })
		})


		function LoadTOC(isSearch, selectNodeID) {
			indicator(true);

			//default list TOC
			var dataOBJ = { action: "GetNodes" } ;
			
			//when coming from SEARCH
			if (isSearch)
				dataOBJ = { action: "GetSearchNodes", s: $('#txtSearch').val().trim() } ;

			$.ajax({
			    url: 'article.php',
				type: "POST",
				data: dataOBJ,
			    dataType: 'json'
			}).done(function(data) {

				if (data) {
					//  if element already created as treeview (aka coming from refresh or search)
					if ($('#toc').hasClass('treeview')) {
						$('#toc').treeview('remove');
					}

					$('#toc').treeview({
						data: data,
						onNodeSelected: function(event, data) {
							console.log(data);

							if (data.nodes!=null)
								$('#toc').treeview('expandNode', [ data.nodeId, { levels: 1, silent: true } ]);
							
							GetArticle();
						},
						onCustomEvent: function(event, node, state) {
							console.log("tahoa");
							console.log(node);
							console.log("tahoa2");
							return CanSelectQ();
					    }
					});

					//collapse all by default
					$('#toc').treeview('collapseAll', {
						silent: true
					});

					//logic when insert / update, after reload select the node via dbase id
					if (selectNodeID)
					{	
						//searches the treeview via 'dbase id' and returns the node
						let selectedNodeID = $('#toc').treeview('getNodeIdByDBid', selectNodeID);
						console.log(selectedNodeID);

						$('#toc').treeview('revealNode', [ selectedNodeID, { silent: true } ]); //neede in case is children, so expand the parents
						$('#toc').treeview('toggleNodeSelected', [ selectedNodeID, { silent: false } ]); //fire the onNodeSelected event > which calling GetArticle function

						//unrem to scroll to selected node
						// $('html, body').animate({
						// 	scrollTop: $('.node-selected').first().offset().top
						// }, 1000);

						return;
					}

					//restore interface
					$('#toc').removeClass('disabled');
					$('#txtArticle').val('');
					$('.summernote').summernote('code', '');
					$('#btnNew').text('new');
					$('#articlePath').html('');

					document.title = data ? ($('#toc').treeview('getNodesCount') + " node(s)") : 'miniKB';
				} else {
						alert("ERROR or is new database!");
				}

				indicator(false);

			}).fail(function(jqXHR, textStatus) {
				indicator(false);
			    alert("Error occurred: " + textStatus);
			})
			// .always(function() {
			//     indicator(false);
			// })
		}
		
		function NewArticle(){
			if ( $('#btnNew').text() == 'new' )
			{ //new
				if (GetSelectedID())
					$('#articlePath').html(GetSelectedText());

				$('#toc').addClass('disabled');
				$('#btnNew').text('cancel');
				//
				$('#txtArticle').val('');
				$('.summernote').summernote('code', '');
			}else { //cancel pressed
				// $('#toc').removeClass('disabled');
				// $('#btnNew').text('new');
				GetArticle();
			}
		}

		function SaveArticle(){
			var parentID = 0;
			var selectedNodeID = 0;
			
			if ($('#toc').hasClass('disabled')) //if is disabled means coming from 'new' button (aka *insert*)
			{
				// console.log();
				if (GetSelectedID()) //user selected a parent
					parentID = GetSelectedID();
				else  // add it to root
					parentID = 0;
			}	
			else { // user try to *update* existing node without click a node
				selectedNodeID = GetSelectedID();
				
				if (selectedNodeID==null) {
					alert("please select parent node, or create a new article, by pressing the 'new' button");
					return;
				}
			}
			
			indicator(true);

			$.ajax({
			    url: 'article.php',
				type: "POST",
				data: { action : 'SaveArticle', id : selectedNodeID, parentID : parentID, articleName : $('#txtArticle').val().trim() , content : $('#contents').val().trim() },
			    dataType: 'json'
			}).done(function(data) {
					// indicator(false); // splitted by event coz LoadTOC indicator
					if (data && data.code == 1){
						LoadTOC(false, data.id); 	//refresh by passing the dbase record id, not handle indicator as LoadTOC takes care
					}
					else {
						indicator(false);
						if (data && data.code == 0)
							alert("ERROR\n\n" + data.message);
						else 
							alert("ERROR");
					}
			}).fail(function(jqXHR, textStatus) {
				indicator(false);
			    alert("Error occurred: " + textStatus);
			}).always(function() {
			    // indicator(false);
			})
		}

		function GetArticle(){ //https://summernote.org/getting-started/#get--set-code

			if (GetSelectedID()==null)
			{
				$('#toc').removeClass('disabled');
				$('#btnNew').text('new');
				return;
			}
				
			indicator(true);

			$.ajax({
			    url: 'article.php',
				type: "POST",
				data: { action: "GetArticle", id: GetSelectedID() },
			    dataType: 'json'
			}).done(function(data) {
				if (data) {
					$('#toc').removeClass('disabled');
					$('#btnNew').text('new');

					$('.summernote').summernote('code', data.NodeCode);
					$('#txtArticle').val(data.NodeName);
					$('#articlePath').html(data.NodePath);
				} else
					 alert("ERROR");
			}).fail(function(jqXHR, textStatus) {
			    alert("Error occurred: " + textStatus);
			}).always(function() {
			    indicator(false);
			})
		}

	
		
		function DeleteArticle(){
			if (GetSelectedID()!=null) {

				if (GetSelectedChildren() != null) {
					alert("you cannot delete node with children");
					return;
				}
				
				if (confirm("Delete '" + GetSelectedText() + '\' ??' ))
				{
					indicator(true);

					$.ajax({
						url: 'article.php',
						type: "POST",
						data: { action: "DeleteArticle", id: GetSelectedID() },
			    		dataType: 'json'
					}).done(function(data) {
						
						if (data && data.code == 1)
							//refresh
							LoadTOC();
						else if (data && data.code == 2) {
							indicator(false);
							alert("ERROR\n\n" + data.message);
						}
						else {
							indicator(false);
							alert("ERROR");
						}

					}).fail(function(jqXHR, textStatus) {
						indicator(false);
						alert("Error occurred: " + textStatus);
					}).always(function() {
						// indicator(false);
					})
					
				}
			} else {
				alert("please select an article");
			}
		}

		function MoveArticle(){

			if (GetSelectedID()==null) {
				alert("please select an article");
				return;
			}

			if ($('#toc').hasClass('disabled')) //if is disabled means coming from 'new' button (aka *insert*)
				return;

			$('#moveNodeModal').modal('toggle');
		}

		function MoveSearchButton(){
			indicator(true);

			$.ajax({
			    url: 'article.php',
				type: "POST",
				data: { action: "MoveSearch", s: $('#parentName').val().trim() },
			    dataType: 'json'
			}).done(function(data) {
				if (data) {
					let entries = "<option value='0'>~~root node~~</option>";
					for (let i = 0; i < data.length; i++)
						entries += "<option value='" + data[i]["NodeID"] + "'>" + data[i]["NodeName"] + "</option>";

					$('#parentNameSelector').html(entries);
					$('#parentNameSelector').change();
				} else
					 alert("ERROR");
			}).fail(function(jqXHR, textStatus) {
			    alert("Error occurred: " + textStatus);
			}).always(function() {
			    indicator(false);
			})
		}

		function MoveSaveButton(){
			indicator(true);

			$.ajax({
			    url: 'article.php',
				type: "POST",
				data: { action: "MoveSave", id: GetSelectedID(), parentID: $('#parentNameSelector').val() },
			    dataType: 'json'
			}).done(function(data) {
				// indicator(false); // splitted by event coz LoadTOC indicator
				if (data && data.code == 1){
					LoadTOC(false, data.id); 	//refresh by passing the dbase record id, not handle indicator as LoadTOC takes care
					$('#moveNodeModal').modal('toggle'); //close modal
				}
				else {
					indicator(false);
					if (data && data.code == 0)
						alert("ERROR\n\n" + data.message);
					else 
						alert("ERROR");
				}
			}).fail(function(jqXHR, textStatus) {
				indicator(false);
			    alert("Error occurred: " + textStatus);
			}).always(function() {
			    // indicator(false);
			})
		}

		function CanSelectQ(){
			let s = GetSelectedID();
			console.log(s);
			if (s==null) return true;

			let proceed = false;

			indicator(true);

			$.ajax({
			    url: 'article.php',
				type: "POST",
				data: { action : 'CanSelectQ', id : s , md : md5($('#contents').val().trim()) },
			    async: false,
				dataType: 'json'				
			}).done(function(data) {
				proceed = data.proceed;
			}).fail(function(jqXHR, textStatus) {
				proceed=false;
			    alert("Error occurred: " + textStatus);
			}).always(function() {
			    indicator(false);
			})

			return proceed;
		}

		function GetSelectedID() {
			var k =  $('#toc').treeview('getSelected');
			if (k.length==0)
				return null;
			else 
				return k[0].href;
    	};

		function GetSelectedText() {
			var k =  $('#toc').treeview('getSelected');
			if (k.length==0)
				return null;
			else 
				return k[0].text;
    	};

		function GetSelectedChildren() {
			var k =  $('#toc').treeview('getSelected');
			if (k.length==0)
				return null;
			else 
			{ 
				if (k[0].nodes==null) {
					return null;
				}
				else 
					return k[0].nodes.length;
			}
    	};
	</script>
  </body>
</html>