<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicWatch Contact Form</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 30px 40px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 8px 8px 0 0;">
                            <!-- Logo and Text Container -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" valign="middle">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <!-- Logo -->
                                                <td valign="middle" style="padding-right: 3px;">
                                                    <img style="height: 60px; display: block;" src="{{ $message->embed(public_path('/Images/Cabulijan/Official Cabulijan Logo.png')) }}" alt="CivicWatch Logo">
                                                </td>
                                                <!-- Text -->
                                                <td valign="middle" style="">
                                                    <table border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-bottom: 2px; border-bottom: 2px solid #ffffff;">
                                                                <p style="margin: 0; font-size: 24px; font-weight: bold; font-family: Poppins, Arial, sans-serif; color: #ffffff;">CivicWatch</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding-top: 4px;">
                                                                <p style="margin: 0; font-size: 11px; font-weight: 600; color: #dbeafe;">REPORT.ACT.RESOLVE</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <h1 style="margin: 20px 0 0 0; color: #ffffff; font-size: 24px; font-weight: 600; text-align: center;">New Contact Form Submission</h1>
                        </td>
                    </tr>

                    <!-- Content Section -->
                    <tr>
                        <td style="padding: 40px;">
                            
                            <!-- Contact Details -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #2563eb; margin-bottom: 10px;">
                                                    <p style="margin: 0; font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Name</p>
                                                    <p style="margin: 5px 0 0 0; font-size: 16px; color: #212529; font-weight: 600;">{{ $data['name'] }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #2563eb;">
                                                    <p style="margin: 0; font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Email</p>
                                                    <p style="margin: 5px 0 0 0; font-size: 16px; color: #212529; font-weight: 600;">{{ $data['email'] }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #2563eb;">
                                                    <p style="margin: 0; font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Subject</p>
                                                    <p style="margin: 5px 0 0 0; font-size: 16px; color: #212529; font-weight: 600;">{{ $data['subject'] }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 20px; background-color: #f8f9fa; border-left: 4px solid #2563eb; border-radius: 4px;">
                                                    <p style="margin: 0 0 10px 0; font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">Message</p>
                                                    <p style="margin: 0; font-size: 15px; color: #495057; line-height: 1.6;">
                                                        {{ $data['message'] }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Reply Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-top: 20px;">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" style="border-radius: 6px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                                    <a href="mailto:{{ $data['email'] }}" style="display: inline-block; padding: 14px 40px; font-size: 16px; color: #ffffff; text-decoration: none; font-weight: 600; letter-spacing: 0.5px;">
                                                        Reply to {{ $data['name'] }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 30px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; font-size: 13px; color: #6c757d; line-height: 1.5; text-align: center;">
                                This email was sent from the CivicWatch contact form.<br>
                                <span style="color: #adb5bd;">Received on November 7, 2025</span>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>