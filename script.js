function toggleMenu() {
    var menu = document.getElementById("menuSidebar");
    menu.classList.toggle("active");
}

// ตรวจจับการคลิกที่ส่วนอื่นของหน้าเว็บ
window.addEventListener("click", function(event) {
    var menu = document.getElementById("menuSidebar");
    var menuButton = document.querySelector(".hamburger-icon"); // ปุ่ม ☰

    // ถ้าคลิกไม่ใช่ที่เมนู หรือปุ่ม ☰ → ซ่อนเมนู
    if (!menu.contains(event.target) && !menuButton.contains(event.target)) {
        menu.classList.remove("active");
    }
});


function fetchCompanyData() {
    let companyName = document.getElementById("company-search").value;

    // เช็คว่าช่องค้นหาว่างหรือไม่
    if (!companyName) {
        // ถ้าว่าง เคลียร์ค่าฟอร์มทั้งหมด
        clearForm();
        return;
    }

    fetch(`fetch_company.php?comp_name=${encodeURIComponent(companyName)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                // เติมค่าฟอร์มเมื่อพบข้อมูล
                document.getElementById("input6").value = data.comp_department || "";
                document.getElementById("input7").value = data.comp_num_add || "";
                document.getElementById("input8").value = data.comp_mu || "";
                document.getElementById("input9").value = data.comp_road || "";
                document.getElementById("input10").value = data.comp_alley || "";
                document.getElementById("input11").value = data.comp_sub_district || "";
                document.getElementById("input12").value = data.comp_district || "";
                document.getElementById("input13").value = data.comp_province || "";
                document.getElementById("input14").value = data.comp_postcode || "";
                document.getElementById("input15").value = data.comp_hr_name || "";
                document.getElementById("input16").value = data.comp_hr_depart || "";
                document.getElementById("input17").value = data.comp_tel || "";
                document.getElementById("input18").value = data.comp_contact || "";
                document.getElementById("input19").value = data.comp_fax || "";
                document.getElementById("comp-id").value = data.comp_id || "";
            } else {
                // เคลียร์ค่าฟอร์มเมื่อไม่พบข้อมูล
                clearForm();
                console.error("Company not found");
            }
        })
        .catch(error => {
            clearForm();
            console.error("Error fetching company data:", error);
        });
}

// ฟังก์ชันสำหรับเคลียร์ค่าฟอร์ม
function clearForm() {
    document.getElementById("input6").value = "";
    document.getElementById("input7").value = "";
    document.getElementById("input8").value = "";
    document.getElementById("input9").value = "";
    document.getElementById("input10").value = "";
    document.getElementById("input11").value = "";
    document.getElementById("input12").value = "";
    document.getElementById("input13").value = "";
    document.getElementById("input14").value = "";
    document.getElementById("input15").value = "";
    document.getElementById("input16").value = "";
    document.getElementById("input17").value = "";
    document.getElementById("input18").value = "";
    document.getElementById("input18").value = "";
    document.getElementById("comp-id").value = "";}

document.getElementById('company-search').addEventListener('input', function() {
    var input = this;
    var datalist = document.getElementById('company-list');
    var options = datalist.getElementsByTagName('option');
    for (var i = 0; i < options.length; i++) {
        if (options[i].value === input.value) {
            var comp_id = options[i].getAttribute('data-id');
            document.getElementById('comp-id').value = comp_id;  // ใส่ comp_id ลงใน hidden input
            break;
        }
    }
});






function toggleCompanyInput() {
    let checkbox = document.getElementById("input5");
    let searchField = document.getElementById("company-search");
    let textField = document.getElementById("comp_name");
    let otherInputs = [
        document.getElementById("input6"),  // ข้อมูลอื่นๆ เช่น ข้อมูลเกี่ยวกับบริษัท
        document.getElementById("input7"),
        document.getElementById("input8"),
        document.getElementById("input9"),
        document.getElementById("input10"),
        document.getElementById("input11"),
        document.getElementById("input12"),
        document.getElementById("input13"),
        document.getElementById("input14"),
        document.getElementById("input15"),
        document.getElementById("input16"),
        document.getElementById("input17"),
        document.getElementById("input18"),
        document.getElementById("input19")
    ];

    if (checkbox.checked) {
        searchField.style.display = "none";
        textField.style.display = "inline-block";
        
        // ทำให้ input อื่นๆ สามารถใช้งานได้
        otherInputs.forEach(input => {
            input.disabled = false; // เปิดใช้งาน input
        });
    } else {
        searchField.style.display = "inline-block";
        textField.style.display = "none";
        
        // ทำให้ input อื่นๆ ถูก disabled
        otherInputs.forEach(input => {
            input.disabled = true; // ปิดการใช้งาน input
        });
    }
}

function toggleMenu() {
    var menu = document.getElementById("menuSidebar");
    menu.classList.toggle("active");
}

// ตรวจจับการคลิกที่ส่วนอื่นของหน้าเว็บ
window.addEventListener("click", function(event) {
    var menu = document.getElementById("menuSidebar");
    var menuButton = document.querySelector(".hamburger-icon"); // ปุ่ม ☰

    // ถ้าคลิกไม่ใช่ที่เมนู หรือปุ่ม ☰ → ซ่อนเมนู
    if (!menu.contains(event.target) && !menuButton.contains(event.target)) {
        menu.classList.remove("active");
    }
});


function fetchCompanyData() {
    let companyName = document.getElementById("company-search").value;

    // เช็คว่าช่องค้นหาว่างหรือไม่
    if (!companyName) {
        // ถ้าว่าง เคลียร์ค่าฟอร์มทั้งหมด
        clearForm();
        return;
    }

    fetch(`fetch_company.php?comp_name=${encodeURIComponent(companyName)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                // เติมค่าฟอร์มเมื่อพบข้อมูล
                document.getElementById("input6").value = data.comp_department || "";
                document.getElementById("input7").value = data.comp_num_add || "";
                document.getElementById("input8").value = data.comp_mu || "";
                document.getElementById("input9").value = data.comp_road || "";
                document.getElementById("input10").value = data.comp_alley || "";
                document.getElementById("input11").value = data.comp_sub_district || "";
                document.getElementById("input12").value = data.comp_district || "";
                document.getElementById("input13").value = data.comp_province || "";
                document.getElementById("input14").value = data.comp_postcode || "";
                document.getElementById("input15").value = data.comp_hr_name || "";
                document.getElementById("input16").value = data.comp_hr_depart || "";
                document.getElementById("input17").value = data.comp_tel || "";
                document.getElementById("input18").value = data.comp_contact || "";
                document.getElementById("input19").value = data.comp_fax || "";
                document.getElementById("comp-id").value = data.comp_id || "";
            } else {
                // เคลียร์ค่าฟอร์มเมื่อไม่พบข้อมูล
                clearForm();
                console.error("Company not found");
            }
        })
        .catch(error => {
            clearForm();
            console.error("Error fetching company data:", error);
        });
}

// ฟังก์ชันสำหรับเคลียร์ค่าฟอร์ม
function clearForm() {
    document.getElementById("input6").value = "";
    document.getElementById("input7").value = "";
    document.getElementById("input8").value = "";
    document.getElementById("input9").value = "";
    document.getElementById("input10").value = "";
    document.getElementById("input11").value = "";
    document.getElementById("input12").value = "";
    document.getElementById("input13").value = "";
    document.getElementById("input14").value = "";
    document.getElementById("input15").value = "";
    document.getElementById("input16").value = "";
    document.getElementById("input17").value = "";
    document.getElementById("input18").value = "";
    document.getElementById("input18").value = "";
    document.getElementById("comp-id").value = "";}

document.getElementById('company-search').addEventListener('input', function() {
    var input = this;
    var datalist = document.getElementById('company-list');
    var options = datalist.getElementsByTagName('option');
    for (var i = 0; i < options.length; i++) {
        if (options[i].value === input.value) {
            var comp_id = options[i].getAttribute('data-id');
            document.getElementById('comp-id').value = comp_id;  // ใส่ comp_id ลงใน hidden input
            break;
        }
    }
});






function toggleCompanyInput() {
    let checkbox = document.getElementById("input5");
    let searchField = document.getElementById("company-search");
    let textField = document.getElementById("comp_name");
    let otherInputs = [
        document.getElementById("input6"),  // ข้อมูลอื่นๆ เช่น ข้อมูลเกี่ยวกับบริษัท
        document.getElementById("input7"),
        document.getElementById("input8"),
        document.getElementById("input9"),
        document.getElementById("input10"),
        document.getElementById("input11"),
        document.getElementById("input12"),
        document.getElementById("input13"),
        document.getElementById("input14"),
        document.getElementById("input15"),
        document.getElementById("input16"),
        document.getElementById("input17"),
        document.getElementById("input18"),
        document.getElementById("input19")
    ];

    if (checkbox.checked) {
        searchField.style.display = "none";
        textField.style.display = "inline-block";
        
        // ทำให้ input อื่นๆ สามารถใช้งานได้
        otherInputs.forEach(input => {
            input.disabled = false; // เปิดใช้งาน input
        });
    } else {
        searchField.style.display = "inline-block";
        textField.style.display = "none";
        
        // ทำให้ input อื่นๆ ถูก disabled
        otherInputs.forEach(input => {
            input.disabled = true; // ปิดการใช้งาน input
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    function updateFileName(inputId, fileNameId, buttonId) {
        var fileInput = document.getElementById(inputId);
        var fileNameDisplay = document.getElementById(fileNameId);
        var uploadButton = document.getElementById(buttonId);

        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;  // แสดงชื่อไฟล์
                fileNameDisplay.classList.remove("empty"); // ลบคลาส empty
                uploadButton.style.display = "none";  // ซ่อนปุ่มเลือกไฟล์หลังจากเลือกไฟล์
            } else {
                fileNameDisplay.textContent = "ยังไม่ได้เลือกไฟล์";  // ถ้ายังไม่ได้เลือกไฟล์
                fileNameDisplay.classList.add("empty");
                uploadButton.style.display = "inline-block";  // แสดงปุ่มเลือกไฟล์เมื่อไม่มีการเลือกไฟล์
            }
        });
    }

    // เรียกใช้ฟังก์ชันสำหรับแต่ละไฟล์
    updateFileName("application-form", "application-file-name", "application-upload-button");
    updateFileName("transcript-form", "transcript-file-name", "transcript-upload-button");
    updateFileName("resume-form", "resume-file-name", "resume-upload-button");
});







