<!doctype html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <title>
            @yield('title')
        </title>
        <style type="text/css">
            * { font-family: 'Helvetica Neue', sans-serif !important; }
            a { color: #49c0b8; text-decoration: none !important; }
            body { font-size: 14px; line-height: 20px; background-color: #eeeeee; max-width: 100%; -webkit-font-smoothing: antialiased; }
            @media only screen and (max-width: 640px) {
                .container { width: 440px !important;}
            }
            @media only screen and (max-width: 479px) {
                .container { width: 280px !important; }
            }
        </style>
        @yield('styles')
    </head>
    <body style="font-family:'Helvetica Neue',sans-serif;">

        <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="padding: 35px 0; background: #eeeeee;">
            <tr>
                <td align="center">
                    <table cellpadding="0" cellspacing="0" border="0" align="center" width="600" style="background-color: #49c0b8; color: #ffffff;" class="container">
                        <tr>
                            <td width="600" align="left" style="padding: 25px 20px 20px 35px;" colspan="2">
                                <a href="http://passporttoprana.com"><img src="{{ asset('img/passporttoprana.png') }}" width="120px" alt="Passport To Prana"></a>
                            </td>
                        </tr>
                        <tr>
                            <td width="600" align="left" style="padding: 20px 35px; background-color: #ffffff; color: #666666;" height="40" colspan="2">
                                @yield('body')

                                <p>- the Passport to Prana Team</p>
                                <p><em>P.S. - Like our <a href="//www.facebook.com/passporttoprana" alt="Our Facebook Page">Facebook</a> page and you will be entered to win cool yoga related gear and prizes!</em></p>
                            </td>
                        </tr>
                        <tr>
                            <td width="600" align="left" style="padding: 0 35px; background-color: #eeeeee; color: #666666;" height="40" colspan="2">
                                <p style="text-align:center;color:#999999;"><small>&copy; {{ date("Y") }} Passport to Prana. All Rights Reserved.</small></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>