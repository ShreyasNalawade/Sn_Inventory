$(document).ready(function () {
  const products = {
    general: [
      {
        name: "Toor Dal",
        rate1kg: "₹ 120",
        rate500g: "₹ 65",
        rate250g: "₹ 35",
      },
      {
        name: "Moong Dal",
        rate1kg: "₹ 110",
        rate500g: "₹ 60",
        rate250g: "₹ 32",
      },
      { name: "Sugar", rate1kg: "₹ 45", rate500g: "₹ 23", rate250g: "₹ 12" },
      {
        name: "Basmati Rice",
        rate1kg: "₹ 150",
        rate500g: "₹ 80",
        rate250g: "₹ 45",
      },
      {
        name: "Atta (Flour)",
        rate1kg: "₹ 40",
        rate500g: "₹ 21",
        rate250g: "₹ 11",
      },
      {
        name: "Tea Powder",
        rate1kg: "₹ 250",
        rate500g: "₹ 130",
        rate250g: "₹ 70",
      },
      {
        name: "Coffee Powder",
        rate1kg: "₹ 300",
        rate500g: "₹ 160",
        rate250g: "₹ 85",
      },
      { name: "Salt", rate1kg: "₹ 20", rate500g: "₹ 11", rate250g: "₹ 6" },
      {
        name: "Besan (Gram Flour)",
        rate1kg: "₹ 70",
        rate500g: "₹ 38",
        rate250g: "₹ 20",
      },
      {
        name: "Poha (Flattened Rice)",
        rate1kg: "₹ 55",
        rate500g: "₹ 30",
        rate250g: "₹ 16",
      },
      {
        name: "Semolina (Rava)",
        rate1kg: "₹ 60",
        rate500g: "₹ 32",
        rate250g: "₹ 17",
      },
      {
        name: "Garlic (fresh)",
        rate1kg: "₹ 120",
        rate500g: "₹ 65",
        rate250g: "₹ 35",
      },
      { name: "Onions", rate1kg: "₹ 40", rate500g: "₹ 21", rate250g: "₹ 11" },
      { name: "Potatoes", rate1kg: "₹ 30", rate500g: "₹ 16", rate250g: "₹ 9" },
      {
        name: "Urad Dal",
        rate1kg: "₹ 130",
        rate500g: "₹ 70",
        rate250g: "₹ 38",
      },
      {
        name: "Chana Dal",
        rate1kg: "₹ 80",
        rate500g: "₹ 42",
        rate250g: "₹ 22",
      },
      { name: "Rajma", rate1kg: "₹ 140", rate500g: "₹ 75", rate250g: "₹ 40" },
      {
        name: "Moong Whole",
        rate1kg: "₹ 115",
        rate500g: "₹ 62",
        rate250g: "₹ 33",
      },
      {
        name: "Masoor Dal",
        rate1kg: "₹ 95",
        rate500g: "₹ 50",
        rate250g: "₹ 27",
      },
    ],
    masala: [
      {
        name: "Mustard Seeds",
        rate1kg: "₹ 100",
        rate250g: "₹ 30",
        rate50g: "₹ 10",
        rate10g: "₹ 5",
      },
      {
        name: "Cumin Seeds",
        rate1kg: "₹ 400",
        rate250g: "₹ 110",
        rate50g: "₹ 25",
        rate10g: "₹ 10",
      },
      {
        name: "Coriander Powder",
        rate1kg: "₹ 150",
        rate250g: "₹ 45",
        rate50g: "₹ 15",
        rate10g: "₹ 7",
      },
      {
        name: "Turmeric Powder",
        rate1kg: "₹ 200",
        rate250g: "₹ 55",
        rate50g: "₹ 18",
        rate10g: "₹ 8",
      },
      {
        name: "Red Chili Powder",
        rate1kg: "₹ 250",
        rate250g: "₹ 70",
        rate50g: "₹ 20",
        rate10g: "₹ 9",
      },
      {
        name: "Black Pepper",
        rate1kg: "₹ 500",
        rate250g: "₹ 135",
        rate50g: "₹ 30",
        rate10g: "₹ 12",
      },
      {
        name: "Cardamom",
        rate1kg: "₹ 800",
        rate250g: "₹ 210",
        rate50g: "₹ 50",
        rate10g: "₹ 20",
      },
      {
        name: "Cloves",
        rate1kg: "₹ 750",
        rate250g: "₹ 195",
        rate50g: "₹ 45",
        rate10g: "₹ 18",
      },
    ],
    oil: [
      {
        name: "Sunny Oil",
        rates: {
          "1L": { purchase: "140", sale: "160" },
          "5L": { purchase: "550", sale: "600" },
          "15L(tin)": { purchase: "1550", sale: "1700" },
          "15L(jar)": { purchase: "1600", sale: "1800" },
        },
      },
      {
        name: "Fortune Oil",
        rates: {
          "1L": { purchase: "145", sale: "165" },
          "5L": { purchase: "570", sale: "620" },
          "15L(tin)": { purchase: "1600", sale: "1750" },
          "15L(jar)": { purchase: "1650", sale: "1850" },
        },
      },
      {
        name: "Dalda Oil",
        rates: {
          "1L": { purchase: "135", sale: "155" },
          "5L": { purchase: "530", sale: "580" },
          "15L(tin)": { purchase: "1500", sale: "1650" },
          "15L(jar)": { purchase: "1550", sale: "1700" },
        },
      },
    ],
  };

  const generalTableBody = $("#generalTable tbody");
  const masalaTableBody = $("#masalaTable tbody");
  const oilViewContainer = $("#oil");
  let activeCategory = "general";

  // Function to render the standard table rows (General/Masala)
  function renderStandardTable(data, tableId) {
    const tbody = $(`#${tableId} tbody`);
    tbody.empty(); // Clear existing rows
    if (data.length === 0) {
      tbody.append(
        '<tr><td colspan="6" class="text-center">No results found.</td></tr>'
      );
      return;
    }

    // Set the table headers based on the tableId
    let headerRow;
    if (tableId === "masalaTable") {
      headerRow = `<tr>
            <th scope="col">Purchase Rate</th>
            <th scope="col">Name</th>
            <th scope="col">10g</th>
            <th scope="col">50g</th>
            <th scope="col">250g</th>
            <th scope="col">1Kg</th>
        </tr>`;
      $(`#${tableId} thead`).html(headerRow);
    } else {
      headerRow = `<tr>
            <th scope="col">Purchase Rate</th>
            <th scope="col">Name</th>
            <th scope="col">1kg / ₹</th>
            <th scope="col">500g / ₹</th>
            <th scope="col">250g / ₹</th>
        </tr>`;
      $(`#${tableId} thead`).html(headerRow);
    }

    $.each(data, function (index, product) {
      const rate1kgValue = parseFloat(product.rate1kg.replace("₹", ""));
      let purchaseRate = "₹ --";
      if (!isNaN(rate1kgValue)) {
        const calculatedRate = Math.round(rate1kgValue * 0.9);
        purchaseRate = `₹ ${calculatedRate}`;
      }
      let newRow;
      if (tableId === "masalaTable") {
        newRow = `
          <tr>
            <td>${purchaseRate}</td>
            <td><b>${product.name}</b></td>
            <td>${product.rate10g || "--"}</td>
            <td>${product.rate50g || "--"}</td>
            <td>${product.rate250g || "--"}</td>
            <td>${product.rate1kg || "--"}</td>
          </tr>`;
      } else {
        newRow = `
          <tr>
            <td>${purchaseRate}</td>
            <td><b>${product.name}</b></td>
            <td>${product.rate1kg || "--"}</td>
            <td>${product.rate500g || "--"}</td>
            <td>${product.rate250g || "--"}</td>
          </tr>`;
      }
      tbody.append(newRow);
    });
  }

  // Function to render the oil view with brand cards and tables
  function renderOilView(data) {
    oilViewContainer.empty();
    if (data.length === 0) {
      oilViewContainer.html('<p class="text-center">No results found.</p>');
      return;
    }
    let brandHtml = "";
    $.each(data, function (index, brand) {
      const brandId = brand.name.replace(/\s+/g, "-").toLowerCase();
      const weights = Object.keys(brand.rates);

      const tableRows = weights
        .map(
          (w) => `
        <tr>
          <td>₹ ${brand.rates[w].purchase}</td>
          <td>${w}</td>
          <td>₹ ${brand.rates[w].sale}</td>
        </tr>
      `
        )
        .join("");

      brandHtml += `
        <div class="card mb-3">
          <div class="card-header bg-transparent d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#${brandId}Details" role="button" aria-expanded="${
        index === 0 ? "true" : "false"
      }" aria-controls="${brandId}Details">
            <h5 class="mb-0">${brand.name}</h5>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="collapse ${
            index === 0 ? "show" : ""
          }" id="${brandId}Details">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered text-center">
                  <thead>
                    <tr class="table-light">
                      <th>Purchase</th>
                      <th>Weight</th>
                      <th>Sale</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${tableRows}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      `;
    });
    oilViewContainer.append(brandHtml);

    // Toggle chevron icon on collapse
    $(".card-header").on("click", function () {
      const icon = $(this).find("i");
      if ($(this).attr("aria-expanded") === "true") {
        icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
      } else {
        icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
      }
    });
  }

  // Main function to decide which view to render
  function updateView(category, searchTerm = "") {
    let currentProducts = products[category];
    let filteredProducts = currentProducts;

    if (searchTerm) {
      filteredProducts = currentProducts.filter((p) =>
        p.name.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    // Hide all tab panes initially
    $(".tab-pane").removeClass("show active");

    if (category === "oil") {
      $("#oil").addClass("show active");
      renderOilView(filteredProducts);
    } else if (category === "general") {
      $("#general").addClass("show active");
      renderStandardTable(filteredProducts, "generalTable");
    } else if (category === "masala") {
      $("#masala").addClass("show active");
      renderStandardTable(filteredProducts, "masalaTable");
    }
  }

  // Initial render
  updateView("general");

  // Live search functionality
  $("#searchInput").on("keyup", function () {
    const searchTerm = $(this).val();
    updateView(activeCategory, searchTerm);
  });

  // Handle tab clicks
  $(".nav-tabs .nav-link").on("click", function () {
    activeCategory = $(this).attr("aria-controls");
    $("#searchInput").val(""); // Clear search input
    updateView(activeCategory);
  });

  // Logout functionality
  $("#logout-btn").on("click", function (e) {
    e.preventDefault(); // Prevent default link behavior
    window.location.href = "login.html";
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
