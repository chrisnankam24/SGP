<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Orange-Cameroun | SGP</title>

    <!-- Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="<?php echo site_url(); ?>assets/vendors/select2/dist/css/select2.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="<?php echo site_url(); ?>assets/css/custom.css" rel="stylesheet">
    <!-- Datetimepicker -->
    <link href="<?php echo site_url(); ?>assets/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>assets/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>assets/css/bootstrap-datetimepicker-standalone.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">      
            <!-- sidebar menu -->
                <?php include_once ('static/menu.php');?>
            <!-- /sidebar menu -->
        </div>
          
          
        <!-- top navigation -->
            <?php $this->session->set_userdata("page", "Vue sur les RIOs");?>
            <?php include_once ('static/header.php');?>
        <!-- /top navigation -->
        
        
        <!-- page content -->
         <div class="right_col" role="main">

          <div class="">
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Recherche du RIO</h2>
                    <form id="formlist" action="<?php echo site_url('Helper/uploadFile'); ?>" method="post" enctype="multipart/form-data">
                        <div class="fileupload fileupload-new fright" data-provides="fileupload">
                            <span class="btn btn-primary btn-file file-upload"><span class="fileupload-new">Liste des numéros</span>
                            <span class="fileupload-exists">Changer</span>         <input type="file" name="fileToUpload" accept=".csv, .xls, .xlsx"/></span>
                            <span class="fileupload-preview"></span>
                            <input id="fileRio" type="hidden" class="hidden" value="<?php echo site_url('api/RioAPI/getRioFile'); ?>">
                            <input id="persoRio" type="hidden" class="hidden" value="<?php echo site_url('api/RioAPI/getRioIndividualMSISDN'); ?>">
                            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
                            <button type="submit" class="btn btn-default loupfile"><span class="fa fa-search"></span></button>
                        </div>
                    </form>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                    <form class="form-horizontal form-label-left" novalidate>

                      <div class="item form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3" id="searchone">
                          <div class="input-group">
                            <input id="portingid" class="form-control" type="text" placeholder="Numéro téléphone" data-inputmask="'mask': '699 99 99 99'">
                            <span class="input-group-btn">
                                <button id="btnsearch" type="button" class="btn btn-default"><span class="fa fa-search"></span></button>
                            </span>
                          </div>
                        </div>
                      </div>
                        
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content 2999[0-1]9[0-3]9-02-237699999999-***  -->

        
        <!-- footer content -->
            <?php include_once ('static/footer.php');?>
        <!-- /footer content -->
        
      </div>
    </div>


    <!-- jQuery -->
    <script src="<?php echo site_url(); ?>assets/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="<?php echo site_url(); ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="<?php echo site_url(); ?>assets/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="<?php echo site_url(); ?>assets/vendors/nprogress/nprogress.js"></script>
    <!-- validator -->
    <script src="<?php echo site_url(); ?>assets/vendors/validator/validator.min.js"></script>
    <!-- Select2 -->
    <script src="<?php echo site_url(); ?>assets/vendors/select2/dist/js/select2.full.min.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="<?php echo site_url(); ?>assets/js/custom.js"></script>
    <!-- jquery.inputmask -->
    <script src="<?php echo site_url(); ?>assets/vendors/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="<?php echo site_url(); ?>assets/js/moment/moment.min.js"></script>
    <script src="<?php echo site_url(); ?>assets/js/moment-local.js"></script>
    <script src="<?php echo site_url(); ?>assets/js/datepicker/daterangepicker.js"></script>
    <!-- bootstrap-datetimepicker -->
    <script type="text/javascript" src="<?php echo site_url(); ?>assets/js/bootstrap-datetimepicker.js"></script>
    
    <!-- validator -->
    <script>
      // initialize the validator function
      validator.message.date = 'not a real date';

      // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
      $('form')
        .on('blur', 'input[required], input.optional, select.required', validator.checkField)
        .on('change', 'select.required', validator.checkField)
        .on('keypress', 'input[required][pattern]', validator.keypress);

      $('.multi.required').on('keyup blur', 'input', function() {
        validator.checkField.apply($(this).siblings().last()[0]);
      });

      $('form').submit(function(e) {
        e.preventDefault();
        var submit = true;

        // evaluate the form using generic validaing
        if (!validator.checkAll($(this))) {
          submit = false;
        }

        if (submit)
          this.submit();

        return false;
      });
    </script>
    <!-- /validator -->
    
    <!-- jquery.inputmask -->
    <script>
      $(document).ready(function() {
        $(":input").inputmask();
      });
    </script>
    <!-- /jquery.inputmask -->
    
    <!-- upload button -->
    <script>
        !function(e){
            var t=function(t,n){
                this.$element=e(t),this.type=this.$element.data("uploadtype")||(this.$element.find(".thumbnail").length>0?"image":"file"),this.$input=this.$element.find(":file");
                if(this.$input.length===0)return;
                this.name=this.$input.attr("name")||n.name,this.$hidden=this.$element.find('input[type=hidden][name="'+this.name+'"]'),this.$hidden.length===0&&(this.$hidden=e('<input type="hidden" />'),this.$element.prepend(this.$hidden)),this.$preview=this.$element.find(".fileupload-preview");
                var r=this.$preview.css("height");
                this.$preview.css("display")!="inline"&&r!="0px"&&r!="none"&&this.$preview.css("line-height",r),this.original={exists:this.$element.hasClass("fileupload-exists"),preview:this.$preview.html(),hiddenVal:this.$hidden.val()},this.$remove=this.$element.find('[data-dismiss="fileupload"]'),this.$element.find('[data-trigger="fileupload"]').on("click.fileupload",e.proxy(this.trigger,this)),this.listen()};
                t.prototype={listen:function(){this.$input.on("change.fileupload",e.proxy(this.change,this)),e(this.$input[0].form).on("reset.fileupload",e.proxy(this.reset,this)),this.$remove&&this.$remove.on("click.fileupload",e.proxy(this.clear,this))},change:function(e,t){if(t==="clear")return;var n=e.target.files!==undefined?e.target.files[0]:e.target.value?{name:e.target.value.replace(/^.+\\/,"")}:null;
                if(!n){this.clear();return}this.$hidden.val(""),this.$hidden.attr("name",""),this.$input.attr("name",this.name);
                if(this.type==="image"&&this.$preview.length>0&&(typeof n.type!="undefined"?n.type.match("image.*"):n.name.match(/\.(gif|png|jpe?g)$/i))&&typeof FileReader!="undefined"){var r=new FileReader,i=this.$preview,s=this.$element;
                    r.onload=function(e){i.html('<img src="'+e.target.result+'" '+(i.css("max-height")!="none"?'style="max-height: '+i.css("max-height")+';"':"")+" />"),s.addClass("fileupload-exists").removeClass("fileupload-new")},r.readAsDataURL(n)}else this.$preview.text(n.name),this.$element.addClass("fileupload-exists").removeClass("fileupload-new")},clear:function(e){this.$hidden.val(""),this.$hidden.attr("name",this.name),this.$input.attr("name","");
                if(navigator.userAgent.match(/msie/i)){var t=this.$input.clone(!0);
                this.$input.after(t),this.$input.remove(),this.$input=t}else this.$input.val("");
                this.$preview.html(""),this.$element.addClass("fileupload-new").removeClass("fileupload-exists"),e&&(this.$input.trigger("change",["clear"]),e.preventDefault())},reset:function(e){this.clear(),this.$hidden.val(this.original.hiddenVal),this.$preview.html(this.original.preview),this.original.exists?this.$element.addClass("fileupload-exists").removeClass("fileupload-new"):this.$element.addClass("fileupload-new").removeClass("fileupload-exists")},trigger:function(e){this.$input.trigger("click"),e.preventDefault()}},e.fn.fileupload=function(n){return this.each(function(){var r=e(this),i=r.data("fileupload");
                i||r.data("fileupload",i=new t(this,n)),typeof n=="string"&&i[n]()})},e.fn.fileupload.Constructor=t,e(document).on("click.fileupload.data-api",'[data-provides="fileupload"]',function(t){var n=e(this);
                if(n.data("fileupload"))return;
                n.fileupload(n.data());
                var r=e(t.target).closest('[data-dismiss="fileupload"],[data-trigger="fileupload"]');
                r.length>0&&(r.trigger("click.fileupload"),t.preventDefault())})
            }(window.jQuery)
    </script>
    <!-- /upload button -->
    
    
    <!-- ajax query -->
    <script>
      
    $(document).ready(function() {
        $('#formlist button[type="submit"]').click(function(e) {
            e.preventDefault();
            var url =  $("#formlist").attr('action');
            var $form = $('#formlist');
            var formdata = (window.FormData) ? new FormData($form[0]) : null;
            var data = (formdata !== null) ? formdata : $form.serialize();
            $.ajax({
               url : url,
               type : 'POST',
               dataType: 'html',
               data: data,
               processData: false,
               contentType: false,
               success : function(code){
                   alert($('fileRio').val());
                   $('a.close.fileupload-exists').trigger("click");
                   alert(code);
               },
               error: function() {
                   alert("error");
               },
               complete : function(){

               }
            });

        });
    });
    </script>
    <!-- /ajax query -->

  </body>
</html>