<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Set Your Password</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;">

<table width="100%" bgcolor="#f4f6f9" cellpadding="0" cellspacing="0">
<tr>
<td align="center">

    <table width="600" bgcolor="#ffffff" style="margin-top:30px; border-radius:10px; overflow:hidden;">

        <!-- Header -->
        <tr>
            <td style="background:#4a305d; padding:20px; text-align:center; color:#fff;">
               <a href="https://cookingchamps.info/admin/dashboard" class="brand-link" target="_other" rel="nofollow"> 
                                <img style="width: 150px" src="https://cookingchamps.info/public/storage/admin_logo/1733383897.png" alt="CookingChamps Logo" class="brand-image "> 
                                <span class="brand-text font-weight-bold"><b></b></span>
            </a>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:30px; color:#333;">
                <h3>Hello </h3>

                <p>Your school account has been successfully created.</p>

                <p>Please click the button below to set your password:</p>

                <div style="text-align:center; margin:30px 0;">
                    <a href="{{ $url }}" 
                       style="background:#4a305d; color:#fff; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold;">
                        Set Your Password
                    </a>
                </div>

                <p>If you did not request this, please ignore this email.</p>

                <p>Thanks,<br>School Management Team</p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#777;">
                © {{ date('Y') }} School System. All rights reserved.
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>