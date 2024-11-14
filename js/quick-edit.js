(function () {
	// Store the original edit function
	var originalInlineEdit = inlineEditPost.edit;

	// Override the edit function
	inlineEditPost.edit = function (id) {
		// Call the original edit function
		originalInlineEdit.apply(this, arguments);

		// Get the post ID
		let postId = 0;
		if (typeof id === "object") {
			postId = parseInt(this.getId(id));
		}
    console.log(postId)

		if (postId > 0) {
			// Get the excerpt content
			const excerptElement = document.getElementById(
				"excerpt_quick_edit_" + postId
			);
			const excerptContent = excerptElement ? excerptElement.textContent : "";

			// Find and populate the excerpt textarea
			const excerptTextarea = document.querySelector(
				'.inline-edit-row textarea[name="excerpt"]'
			);
			if (excerptTextarea) {
				excerptTextarea.value = excerptContent;
			}
		}
	};
})();
