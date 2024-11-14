// Store the original quick edit function
const originalInlineEdit = inlineEditPost.edit;

// Override the quick edit function
inlineEditPost.edit = function (id) {
	// Call the original quick edit function
	originalInlineEdit.apply(this, arguments);

	// Get post ID
	let post_id = 0;
	if (typeof id == "object") {
		post_id = parseInt(this.getId(id));
	}

	// Get the excerpt
	if (post_id > 0) {
		// Get the raw excerpt content
		const excerptDiv = document.getElementById("excerpt-" + post_id);
		const excerpt = excerptDiv ? excerptDiv.innerHTML : "";

		// Log for debugging
		console.log("Loading excerpt for post " + post_id + ": ", excerpt);

		// Populate the excerpt field
		const excerptTextarea = document.querySelector(
			'.inline-edit-row textarea[name="excerpt"]'
		);
		if (excerptTextarea) {
			excerptTextarea.value = excerpt;
		}
	}
};

// Add change event listener to verify the textarea is updating
document.addEventListener("DOMContentLoaded", function () {
	document.addEventListener("change", function (e) {
		if (e.target && e.target.matches('textarea[name="excerpt"]')) {
			console.log("Excerpt changed to: ", e.target.value);
		}
	});
});
