$(document).ready(function () {
    // Initial active category
    let activeCategory = "general";

    // Function to filter the tables based on search input
    function filterTable(searchTerm) {
        const tableId = `${activeCategory}Table`;
        const rows = $(`#${tableId} tbody tr`);

        rows.each(function () {
            const row = $(this);
            const rowText = row.text().toLowerCase();
            if (rowText.includes(searchTerm.toLowerCase())) {
                row.show();
            } else {
                row.hide();
            }
        });
    }

    // Function to filter the oil brands based on search input
    function filterOilBrands(searchTerm) {
        const oilCards = $("#oil .card");

        oilCards.each(function () {
            const card = $(this);
            const cardTitle = card.find(".card-header h5").text().toLowerCase();
            if (cardTitle.includes(searchTerm.toLowerCase())) {
                card.show();
            } else {
                card.hide();
            }
        });
    }

    // Live search functionality
    $("#searchInput").on("keyup", function () {
        const searchTerm = $(this).val();
        if (activeCategory === "oil") {
            filterOilBrands(searchTerm);
        } else {
            filterTable(searchTerm);
        }
    });

    // Handle tab clicks
    $(".nav-tabs .nav-link").on("click", function () {
        activeCategory = $(this).attr("data-bs-target").replace("#", "");
        $("#searchInput").val(""); // Clear search input on tab change

        // Show/hide the relevant content for the new tab
        if (activeCategory === "oil") {
            $("#oil").addClass("show active");
            $("#general").removeClass("show active");
            $("#masala").removeClass("show active");
            $("#other").removeClass("show active");
        } else if (activeCategory === "general") {
            $("#general").addClass("show active");
            $("#oil").removeClass("show active");
            $("#masala").removeClass("show active");
            $("#other").removeClass("show active");
        } else if (activeCategory === "masala") {
            $("#masala").addClass("show active");
            $("#general").removeClass("show active");
            $("#oil").removeClass("show active");
            $("#other").removeClass("show active");
        } else if (activeCategory === "other") {
            $("#other").addClass("show active");
            $("#general").removeClass("show active");
            $("#oil").removeClass("show active");
        }
    });

    // Toggle chevron icon on collapse
    $("#oil").on("click", ".card-header", function () {
        const icon = $(this).find("i");
        if ($(this).attr("aria-expanded") === "true") {
            icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
        } else {
            icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
        }
    });

    // Ensure sidebar closes when a link is clicked on mobile
    $(".sidebar-link").on("click", function () {
        if ($(window).width() < 992) {
            $("#sidebar").removeClass("show");
        }
    });

    // Toggle sidebar
    $(".navbar-toggler").on("click", function () {
        $("#sidebar").toggleClass("show");
    });
});
