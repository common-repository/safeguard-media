//////////////////////////////////////////////////////////////
//This file handles the AJAX based uploading of mp4 files.//
//////////////////////////////////////////////////////////////

var WPSM_Popup_Uploader = null;
var wpsafeguard_process_setting = null;

jQuery(document).ready(function ($) {

  WPSM_Popup_Uploader = {

    dialog : null,

    init : function() {
      let that = this;

      that.dialog = $( "#wpsafeguard_div" ).dialog({
        autoOpen: false,
        position: { my: "center top+100", at: "top" },
        width: 850,
        modal: true,
        buttons: {
          Cancel: function() {
            WPSM_Popup_Uploader.dialog.dialog( "close" );
          }
        }
      });

      $('#wpsafeguard_div').on('click', '#wpsm_setting_cancel', function() {
        that.clear();
      });

      $('#wpsafeguard_div').on('click', '#wpsm_setting_save', function() {
        that.saveSettings(this);
      });

      $('#wpsafeguard_div').on('click', '.wpsm-send-editor', function(e) {
        e.preventDefault();

        that.send_editor($(this).attr('data-filename'));
      });

      $('#wpsm-upload-tabs').tabs();

      $('#wpsafeguard_link').on('click', function(e) {
        e.preventDefault();

        WPSM_Popup_Uploader.dialog.dialog( "open" );
      });

      that.load_files();
    },

    clear : function() {
      $('#upload-filename').html('');
      $('#upload-insert-form').html('');
      $('#upload-status').html('');
    },

    load_files : function() {
      $("#wpsafeguard_upload_list").html(
        `<tr>
          <td colspan="2" style="text-align: center;">Loading files...</td>
        </tr>`
      );

      let request = {
        nonce: wpsm_safeguard_uploader_data.nonce,
      };

      $.post(ajaxurl + '?action=wpsm_get_server_files', request, function(data) {
        let html = '';

        $.each(data, function(index, value) {
          html +=
            `<tr>
              <td><a href="#" data-filename="${value.file}" class="wpsm-send-editor row-actionslink">${value.title}</a></td>
              <td>${value.date}</td>
            </tr>`;
        });

        $("#wpsafeguard_upload_list").html(html);
      });
    },

    saveSettings : function(obj) {
      let that = this;
      let postid   = that.getPostID();
      let setData  = {};
      let filename = $(obj).attr('data-filename');

      $("#wpsafeguard_setting_body input").each(function () {
        var nname = $(this).attr("name");
        if (nname == "print_anywhere" || nname == "allow_capture" || nname == "allow_remote") {
          setData[nname] = ($(this).attr("checked") == "checked") ? "checked" : "";
        } else {
          setData[nname] = $(this).val();
        }
      });

      let request = {
        action: 'wpsm_save_file_settings',
        post_id: postid,
        nname: filename,
        set_data: setData,
        nonce: wpsm_safeguard_uploader_data.nonce
      };

      wpsafeguard_process_setting("setting", "start");

      $.post(ajaxurl, request, function (param) {
        $("#wpsafeguard_message").html(param.message);
        wpsafeguard_process_setting("setting", "end");
        that.send_editor(filename);
        that.clear();
        WPSM_Popup_Uploader.load_files();
      });
    },

    send_editor : function(name) {
      let that = this;
      let postid = that.getPostID();

      wpsafeguard_process_setting('sendeditor', 'start');

      let request = {
        action: 'wpsm_get_file_settings',
        filename: name,
        post_id: postid,
        nonce: wpsm_safeguard_uploader_data.nonce
      };

      $.post(ajaxurl, request, function (param) {
        let file_text = "\n[safeguard name='" + name + "' " + param.parameters + "]"

        if(that.isTinyMCEActive()) {
          tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent() + file_text);
        } else {
          $('#content').val($('#content').val() + file_text);
        }

        WPSM_Popup_Uploader.dialog.dialog( "close" );
        wpsafeguard_process_setting('sendeditor', 'end');
      });
    },

    isTinyMCEActive : function() {
      return (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden();
    },

    isBlocked : function( node ) {
      return $(node).is( '.processing' ) || $(node).parents( '.processing' ).length;
    },

    block : function( node ) {
      if ( ! this.isBlocked( node ) ) {
        $(node).addClass( 'processing' ).append('<div class="processing-blocker"></div>');
      }
    },

    unblock : function( node ) {
      $(node).removeClass( 'processing' ).find('.processing-blocker').remove();
    },

    getPostID: function() {
      return $("#post_ID").val();
    }
  };

  /***************************
   * Upload
   **************************/
  wpsafeguard_process_setting = function (frm, status) {

    if (status == "start") $("#wpsafeguard_ajax_process").show();
    if (status == "end") $("#wpsafeguard_ajax_process").hide();

    if (frm == "load") {
      if (status == "start") {
        $("#wpsafeguard_message").html("");
        $('input:button').attr("disabled", true);
      }

      if (status == "end") {
        prequeue = "";
        $("#custom-queue").html("No file chosen");
        $('input:button').attr("disabled", false);
      }
    }

    if (frm == "search") {
      if (status == "start") {
        $("#search").attr("disabled", true);
      }

      if (status == "end") {
        $("#search").attr("disabled", false);
      }
    }

    if (frm == "setting") {
      if (status == "start") {
      }
      if (status == "end") {
      }
    }
  }

  if ($('.mfu-plugin-uploader').length > 0) {
    var options   = false;
    var container = $('.mfu-plugin-uploader');

    options = JSON.parse(JSON.stringify(global_uploader_options));

    if (container.hasClass('multiple')) {
      options['multi_selection'] = true;
    }

    var new_url = window.location.host;

    var uploader = new plupload.Uploader({
      browse_button: 'mfu-plugin-uploader-button', // this can be an id of a DOM element or the DOM element itself
      runtimes: 'html5,flash,silverlight,gears,html4',
      flash_swf_url: '/wp-includes/js/plupload/plupload.flash.swf',
      silverlight_xap_url: '/wp-includes/js/plupload/plupload.silverlight.xap',
      urlstream_upload: true,
      file_data_name: 'async-upload',
      multipart: true,
      multi_selection: false,
      resize: {width: 300, height: 300, quality: 90},
      multipart_params: {nonce: wpsm_safeguard_uploader_data.nonce, action: 'wpsm_upload'},
      url: 'admin-ajax.php',
      filters: [
        {title: "MP4 files", extensions: "mp4"},
        {title: "Image files", extensions: "jpg,jpeg,gif,png"},
        {title: "PDF files", extensions: "pdf"},
        {title: "DOC files", extensions: "doc,docx"}
      ]
    });

    uploader.init();

    // EVENTS
    // init
    uploader.bind('Init', function (up) {
      
    });

    // error
    uploader.bind('Error', function (up, args) {
      if( args["code"] == '-600' ){
        $("#wpsafeguard_message").html('<div class="error"><p>'+args["message"]+' <b>Please upload file less than '+global_uploader_options.max_file_size+' of size.</b></p></div>');
      }

      if( args["code"] == '-601' ){
        $("#wpsafeguard_message").html('<div class="error"><p>'+args["message"]+' <b>Please upload only .mp4 file.</b></p></div>');
      }
    });

    // file added
    uploader.bind('FilesAdded', function (up, files) {

      $.each(files, function (i, file) {
        $("#upload-filename").html(file.name);
        $("#upload-status").html("Upload Started");
      });

      up.refresh();
      up.start();
    });

    // file uploaded
    uploader.bind('FileUploaded', function (up, file, response) {

      response = $.parseJSON(response.response);

      if (response['status'] == 'success') {
        $("#upload-status").html("Upload Complete");

        let file_name = file.name;

        /***********************************************
         * Display message based on the status of the
         * uploaded file
         **********************************************/
        let request = {
          action: 'wpsm_save_uploaded_file',
          url: response.attachment.src,
          post_id: WPSM_Popup_Uploader.getPostID(),
          nonce: wpsm_safeguard_uploader_data.nonce
        };

        $.post(ajaxurl, request, function (param) {
          wpsafeguard_process_setting("load", "end");
          if(param.success) {
            $('#upload-insert-form').html(param.option_form);
          }

          $("#wpsafeguard_message").html(param.message);
        });
      }
      else {
        $("#upload-status").html("Error Uploading File");
      }
    });
  }

  WPSM_Popup_Uploader.init();
});