function handlePagination(pageKey, fetchUrl, targetElement) {
    // Retrieve the saved page from sessionStorage
    var savedPage = sessionStorage.getItem(pageKey);

    // If a page number is saved, load that page; otherwise, load the default page
    if (savedPage) {
        fetch(fetchUrl + "?page=" + savedPage)
            .then((response) => response.text())
            .then((html) => {
                document.querySelector(targetElement).innerHTML = html;
            });
    }

    // Handle click on pagination links
    $("body").on("click", ".pagination a", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");

        // Get the page number from the URL
        var page = url.split("page=")[1];

        // Store the page number in sessionStorage
        sessionStorage.setItem(pageKey, page);

        // Fetch and update the target element with new content
        $.get(url, $("#search").serialize(), function (data) {
            $(targetElement).html(data);
        });
    });
}
