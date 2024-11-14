document.addEventListener("DOMContentLoaded", function () {
	var excerptFieldHTML =
		'<div class="inline-edit-col"><label><span class="title">Excerpt</span><span class="input-text-wrap"><input type="text" name="excerpt" class="ptitle" value=""></span></label></div>';

	// Function to append excerpt field to Quick Edit row
	function appendExcerptField() {
		var editRows = document.querySelectorAll(
			".inline-edit-row .inline-edit-col:last-child"
		);
		editRows.forEach(function (editRow) {
			editRow.insertAdjacentHTML("beforeend", excerptFieldHTML);
		});
	}

	// Function to populate the excerpt field when Quick Edit is clicked
	function populateExcerptField() {
		var editLinks = document.querySelectorAll(".editinline");
		editLinks.forEach(function (editLink) {
			editLink.addEventListener("click", function () {
				var postRow = this.closest("tr");
				var postID = postRow.id;
				var excerpt = postRow.querySelector(".post-excerpt").textContent;
				var excerptInput = document.querySelector(
					'.inline-edit-row input[name="excerpt"]'
				);
				if (excerptInput) {
					excerptInput.value = excerpt;
				}
			});
		});
	}

	// Function to save the excerpt when Quick Edit is submitted
	function saveExcerpt() {
		var saveButtons = document.querySelectorAll('button[type="button"].save');
		saveButtons.forEach(function (saveButton) {
			saveButton.addEventListener("click", function () {
				var postRow = this.closest("tr");
				var postID = postRow.id.replace("post-", "");
				var excerpt = document.querySelector(
					'.inline-edit-row input[name="excerpt"]'
				).value;
				var data = new FormData();
				data.append("action", "eqe_save_excerpt");
				data.append("post_id", postID);
				data.append("excerpt", excerpt);
				data.append("nonce", EQEData.nonce);

				fetch(ajaxurl, {
					method: "POST",
					body: data,
				})
					.then((response) => response.text())
					.then((response) => {
						if (response === "success") {
							postRow.querySelector(".post-excerpt").textContent = excerpt;
						}
					});
			});
		});
	}

	appendExcerptField();
	populateExcerptField();
	saveExcerpt();
});
