document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("categoryPieChart");
  if (ctx && typeof catNamesData !== "undefined" && catNamesData.length > 0) {
    new Chart(ctx.getContext("2d"), {
      type: "doughnut",
      data: {
        labels: catNamesData,
        datasets: [
          {
            data: subCountsData,
            backgroundColor: [
              "#0d00ff",
              "#0ef544",
              "#108799",
              "#ffea07",
              "#ff0019",
              "#6607ff",
              "#f26e10",
            ],
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        plugins: {
          legend: { display: false },
        },
        cutout: "70%",
      },
    });
  }
});


/* ==================================================
                  CONTACT
================================================== */
let current_conv_id = null;

function loadUserList() {
  $.ajax({
    url: "pages/ajax_chat_admin.php?action=load_list",
    success: function (data) {
      $("#admin-conversation-list").html(data);
    },
  });
}

function openChat(conv_id) {
  current_conv_id = conv_id;
  $.ajax({
    url: "pages/ajax_chat_admin.php?action=open_chat&conv_id=" + conv_id,
    success: function (data) {
      $("#admin-chat-area").html(data);
      scrollChatBottom();
    },
  });
}

function loadMessages() {
  if (!current_conv_id) return;
  $.ajax({
    url: "pages/ajax_chat_admin.php?action=load_messages&conv_id=" + current_conv_id,
    success: function (data) {
      const body = $("#admin-chat-body");
      if (body.html() !== data) {
        body.html(data);
        scrollChatBottom();
      }
    },
  });
}

function sendAdminMsg() {
  const input = $("#admin-chat-input");
  const msg = input.val().trim();
  if (!msg || !current_conv_id) return;

  $.ajax({
    url: "pages/ajax_chat_admin.php",
    type: "POST",
    data: { action: "send", conv_id: current_conv_id, message: msg },
    success: function () {
      input.val("");
      loadMessages();
    },
  });
}

function scrollChatBottom() {
  const body = document.getElementById("admin-chat-body");
  if (body) body.scrollTop = body.scrollHeight;
}

// Chạy định kỳ
setInterval(loadUserList, 5000); // Cập nhật danh sách user mỗi 5s
setInterval(loadMessages, 2000); // Cập nhật tin nhắn mỗi 2s
loadUserList();
