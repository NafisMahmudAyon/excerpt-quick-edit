document.addEventListener("DOMContentLoaded", function () {
	console.log("DOM fully loaded and parsed");
	const originalInlineEdit = inlineEditPost.edit;

	// Override the inlineEditPost.edit function
	inlineEditPost.edit = function (id) {
		originalInlineEdit.apply(this, arguments);

		let postId = 0;
		if (typeof id === "object") {
			postId = parseInt(this.getId(id));
		}

		if (postId > 0) {
			const excerptDiv = document.getElementById("excerpt-" + postId);
			const excerpt = excerptDiv ? excerptDiv.innerHTML : "";
			console.log("Loading excerpt for post ID " + postId + ": ", excerpt);

			const excerptTextarea = document.querySelector(
				'.inline-edit-row textarea[name="excerpt"]'
			);
			console.log(excerptTextarea);
			if (excerptTextarea) {
				excerptTextarea.value = excerpt;
				console.log("Excerpt field populated for post ID " + postId);
			}
		}
	};

	document.addEventListener("click", function (event) {
		console.log("Document was clicked"); // Debugging line

		// Check if the clicked element or its parent is the save button
		const isMatchingTarget =
			event.target.matches('button[type="button"].save') ||
			event.target.closest('button[type="button"].save');
			// console.log(document.querySelector('.save')) ;
			

		// if (isMatchingTarget && window.inlineEditPost) {
		if (event.target.matches(".update-excerpt")) {
			console.log("Save button clicked"); // Debugging line
			const postId = window.inlineEditPost.getId(event.target);
			const excerptValue = document.querySelector(
				'.inline-edit-row textarea[name="excerpt"]'
			).value;

			// Perform an AJAX request to save the excerpt
			const data = new FormData();
			data.append("action", "save_quick_edit_excerpt");
			data.append("post_id", postId);
			data.append("excerpt", excerptValue);
			data.append("_ajax_nonce", excerptQuickEdit.nonce);

			fetch(ajaxurl, {
				method: "POST",
				body: data,
			})
				.then((response) => response.json())
				.then((response) => {
					console.log("AJAX response received:", response);
					if (response.success) {
						console.log("Excerpt successfully saved for post ID " + postId);
						window.location.reload();
					} else {
						console.log(
							"Error saving excerpt for post ID " + postId + ": ",
							response.data
						);
					}
				})
				.catch((error) => {
					console.log(
						"AJAX error while saving excerpt for post ID " + postId + ": ",
						error
					);
				});
		}
	});
});
