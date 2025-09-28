<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
    <body style="width: 100%; height: 100%; display: flex; justify-content: center; justify-items: center;">
        <table style role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center">
                    <table border="0" cellpadding="0" cellspacing="0" width="600">
                        <tr>
                            <td align="center">
                                <img style="height: 60px;" src="{{ $message->embed(public_path('/Images/CivicWatch(2).png')) }}" alt="CivicWatch Logo">
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <p><strong>Name:</strong> {{ $data['name'] }}</p>
                                <p><strong>Email:</strong> {{ $data['email'] }}</p>
                                <p><strong>Subject:</strong> {{ $data['subject'] }}</p>
                                <p><strong>Message:</strong></p>
                                <p>{{ $data['message'] }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td align="center">
                                <a href="mailto:{{ $data['email'] }}">
                                    Reply to {{ $data['name'] }}
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
