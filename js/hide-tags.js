document.addEventListener("DOMContentLoaded", function () {
    // Select the container with the tags
    const taggedAs = document.querySelector(".product_meta .tagged_as");

    if (taggedAs) {
        // List of tags to hide
        const tagsToHide = [
            "chattanooga",
            "Kinseys",
            "Lipseys",
            "RSR",
            "sportssouth",
            "zanders"
        ];

        // Get all tag links in the "tagged_as" section
        const tagLinks = taggedAs.querySelectorAll("a[rel='tag']");

        // Iterate through the tag links and hide matching tags
        tagLinks.forEach((link) => {
            const tagText = link.textContent.trim();

            // Check if the tag is in the list of tags to hide
            if (tagsToHide.includes(tagText)) {
                // Remove the tag link and its comma
                const parent = link.parentNode;
                if (parent) {
                    // Remove the following comma, if present
                    if (link.nextSibling && link.nextSibling.nodeType === Node.TEXT_NODE) {
                        const nextText = link.nextSibling.textContent.trim();
                        if (nextText.startsWith(',')) {
                            link.nextSibling.textContent = nextText.slice(1).trim();
                        }
                    }
                    // Remove the link itself
                    parent.removeChild(link);
                }
            }
        });

        // Clean up extra commas or spaces in the remaining content
        let updatedContent = taggedAs.innerHTML;

        // Remove sequences of commas and normalize spacing
        updatedContent = updatedContent
            .replace(/,(\s*,)+/g, ',') // Replace multiple commas with a single one
            .replace(/^,|,$/g, '') // Remove leading or trailing commas
            .replace(/\s+,|,\s+/g, ', ') // Normalize spacing around commas
            .trim();

        // Update the container content
        taggedAs.innerHTML = updatedContent;
    }
});
