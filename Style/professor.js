document.querySelector('form').onsubmit = function(event) {
    // หาค่า checkbox ที่ถูกเลือก
    var checkboxes = document.querySelectorAll('input[name="student_ids[]"]:checked');
    
    // ถ้าไม่มี checkbox ถูกเลือก
    if (checkboxes.length === 0) {
        alert("กรุณาเลือกนักศึกษาก่อนยืนยัน");
        event.preventDefault();  // หยุดการส่งฟอร์ม
    }
};
