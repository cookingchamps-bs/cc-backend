<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Reset Password</title>
   </head>
   <body>
      <div style="max-width: 600px; display: block; margin: auto;font-family: sans-serif;">
         <!-- <div style="width: 100%; padding: 30px 0px;">
            <img style="width: 115px;" src="https://cloudwapp.in/BH/Cleansea/static/web/assets/image/emaillogo.svg" />
            </div> -->
         <div style="padding: 0px 0px;">
            <div style="width: 100%;background:#4a305d; padding: 0px 0px 0px 0px;box-shadow: 0px 0px 5px #b3b3b3c4;">
               <div>
                  @php
                  $logo = 'https://work.mobidudes.in/CH/CookingChamps/public/storage/logo.png';
                  @endphp
                  <section style="width: 100%;">
                     <img style="width: 180px; padding-top: 20px; padding-left: 10px;margin:auto;display: block;" src="{{$logo}}" alt="Company Logo" />
                     <table class="table table-borderless" style="width: 100%;background: #fff; max-width:600px;padding: 25px 10px;">
                        <tbody>
                           <tr style="position: relative; font-size: 18px;">
                              <td style="width:100%; text-align: left; color:#222; font-weight:600; padding-bottom: 10px; max-width:450px; padding-left: 10px; "> Hi <span> {{$username}},</span></td>
                           </tr>
                           <tr style="position: relative; font-size: 16px;">
                              <td style="width:100%; text-align: left; color:#6a6a6a; font-weight:500; padding-bottom: 30px; max-width:450px; padding-left: 10px; ">
                                 We received a request to reset your CookingChamps password.Please use the one-time password (OTP) below to securely set a new password:
                              </td>
                           </tr>
                           <tr style="position: relative; font-size: 14px;">
                              <td style="width:100%; text-align: left; color:#222; font-weight:500; padding-bottom: 10px; max-width:450px; padding-left: 10px; ">
                                 <a href="javascript:;" style="background:#4a305d;border:1px solid #4b1cc6;padding: 10px 10px;font-weight: 600; font-size: 17px;
                                    color: #fff;border-radius: 5px;cursor: pointer;margin-top: 15px; text-decoration:none;">Your OTP:{{$otp}}</a>
                              </td>
                           </tr>
                           <tr style="position: relative; font-size: 16px; mt:3px">
                              <td style="width:100%; text-align: left; color:#6a6a6a; font-weight:500; padding-bottom: 10px;padding-top: 30px; max-width:450px; padding-left: 10px; ">
                                 This code will be valid for the next 5 minutes. If you didn't request a password reset, please ignore this email or contact our support team.
                              </td>
                           </tr>
                           <tr style="position: relative; font-size: 16px; ">
                              <td style="width: 100%;text-align: left;color: #6a6a6a;font-weight: 500;max-width: 450px;padding-top: 10px; padding-left: 10px;">Best Regards,</td>
                           </tr>
                           <tr style="position: relative; font-size: 16px; ">
                              <td style="width: 100%;text-align: left;color: #6a6a6a;font-weight: 500;padding-bottom: 10px;max-width: 450px; padding-left: 10px;">The CookingChamps Team</td>
                           </tr>
                        </tbody>
                     </table>
                  </section>
                  <div style="width: 100%; background:#f0f0f0; padding: 20px 0px;">
                     <p style="margin: 0; text-align: center; font-size: 14px; font-weight: 600; color:#000;"> Copyrights © 2024 CookingChamps.</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
      </div>
   </body>
</html>