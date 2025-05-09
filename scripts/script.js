// Register AJAX
$(document).ready(function () {
  $('#registerForm').on('submit', function (e) {
    e.preventDefault();

    const formData = {
      action: 'register',
      username: $('#username').val(),
      email: $('#email').val(),
      password: $('#password').val(),
      confirm_password: $('#confirm_password').val()
    };

    $.ajax({
      url: '../core/handleForms.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.result) {
          alert(response.message);
          window.location.href = "login.php";
        } else {
          alert(response.message);
        }
      },
      error: function () {
        alert('Something went wrong with the server.');
      }
    });
  });
});

// Login AJAX
$(document).ready(function () {
  $('#loginForm').on('submit', function (e) {
    e.preventDefault(); // Prevents the default form submission

    const formData = {
      action: 'login', // Sending action type for backend to identify
      email: $('#username').val(),
      password: $('#password').val(),
    };

    $.ajax({
      url: '../core/handleForms.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.result) {
          alert(response.message);
          window.location.href = "../index.php"; // Redirect on success
        } else {
          alert(response.message); // Show error message from response
        }
      },
      error: function () {
        alert('Something went wrong with the server.');
      }
    });
  });
});

// Edit Button Click Handler For Product Reviews
$(document).ready(function() {
    // Handle edit button click
    $('.editBtn').click(function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const productDescription = $(this).data('description');

        // Hide all edit forms first
        $('.editFormContainer').hide();

        // Show only the edit form for this product
        const editForm = $('#editFormContainer-' + productId);
        editForm.show();

        // Populate the form fields
        editForm.find('input[name="product_name"]').val(productName);
        editForm.find('textarea[name="description"]').val(productDescription);

        // Scroll to the edit form
        $('html, body').animate({
            scrollTop: editForm.offset().top - 20
        }, 200);
    });

    // Handle delete button click
    $('.deleteBtn').click(function() {
        const productId = $(this).data('id');
        const productElement = $(this).closest('.product-review');

        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: 'core/handleForms.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    product_id: productId,
                    deleteBtn: 1
                },
                success: function(response) {
                    if (response.status === 'success') {
                        productElement.fadeOut(300, function() {
                            $(this).remove();
                        });
                        loadAuditLog(); // Refresh audit log after the deletion
                    } else {
                        alert(response.message || 'Error deleting product');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Server error'));
                }
            });
        }
    });

    // Handle cancel button click for the edit form
    $('.cancelEdit').click(function() {
        $(this).closest('.editFormContainer').hide();
    });

    // Handle form submission for the edit form
    $('.editProductForm').submit(function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const formData = $(this).serialize();

        $.ajax({
            url: 'core/handleForms.php',
            type: 'POST',
            data: formData + '&updateProductBtn=1',
            success: function(response) {
                alert('Product updated successfully');
                loadAuditLog(); // Refresh audit log after the update
                location.reload();
            },
            error: function() {
                alert('Error updating product');
            }
        });
    });
});

// Edit and Delete Button Click Handler For Comments
$(document).ready(function() {
    // Edit button click handler
    $(document).on('click', '.editCommentBtn', function() {
        const id = $(this).data('id');
        console.log('Edit comment ID:', id);

        // Hide display elements
        $(`#rating-display-${id}, #comment-text-${id}`).addClass('hidden');
        
        // Show edit elements
        $(`#rating-edit-${id}, #comment-edit-${id}, .saveCommentBtn[data-id="${id}"]`)
            .removeClass('hidden')
            .addClass('block');
    });

    // Save button click handler
    $(document).on('click', '.saveCommentBtn', function() {
        const id = $(this).data('id');
        const updatedRating = $(`#rating-edit-${id}`).val();
        const updatedComment = $(`#comment-edit-${id}`).val();

        $.ajax({
            url: 'core/handleForms.php',
            type: 'POST',
            dataType: 'json',
            data: {
                review_id: id,
                review_text: updatedComment,
                stars: updatedRating,
                updateCommentBtn: 1
            },
            success: function(response) {
                if (response.status === 'success') {
                    // Update display
                    $(`#rating-display-${id}`).text(`${updatedRating}/5`).removeClass('hidden');
                    $(`#comment-text-${id}`).text(updatedComment).removeClass('hidden');
                    
                    // Hide edit fields
                    $(`#rating-edit-${id}, #comment-edit-${id}, .saveCommentBtn[data-id="${id}"]`)
                        .addClass('hidden');
                    
                    loadAuditLog(); // Refresh audit log after comment update
                    alert('Comment updated successfully');
                } else {
                    alert(response.message || 'Failed to update comment');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                alert('Error updating comment. Check console for details.');
            }
        });
    });

    // Delete comment button click handler
    $(document).on('click', '.deleteCommentBtn', function() {
        const commentId = $(this).data('id');
        const commentElement = $(this).closest('li');

        if (confirm('Are you sure you want to delete this comment?')) {
            $.ajax({
                url: 'core/handleForms.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    review_id: commentId,  // Changed from comment_id to review_id
                    deleteCommentBtn: 1
                },
                success: function(response) {
                    if (response.status === 'success') {
                        commentElement.fadeOut(300, function() {
                            $(this).remove();
                        });
                        loadAuditLog(); // Refresh audit log after comment deletion
                    }
                    alert(response.message);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    alert('Error deleting comment. Check console for details.');
                }
            });
        }
    });
});

// Function to load the audit log
function loadAuditLog() {
    fetch('audit-log.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('audit-log-container').innerHTML = html;
        });
}

// Initial audit log load and periodic refresh every 5 seconds
loadAuditLog();
setInterval(loadAuditLog, 5000);
