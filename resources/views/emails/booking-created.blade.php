<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>הזמנה התקבלה</title>
</head>
<body style="font-family: Arial; background-color: #f5f5f5; padding: 20px;">

<div style="background-color: white; padding: 20px; border-radius: 10px;">
    <h2 style="color: #7b1e3a;">ההזמנה שלך התקבלה בהצלחה 🍷</h2>

    <p>שלום {{ $booking->user->name ?? 'לקוח יקר' }},</p>

    <p>
        הרכישה שלך בוצעה בהצלחה.
    </p>

    <p>
        סטטוס ההזמנה שלך כרגע:
        <strong>{{ $statusHebrew }}</strong>
    </p>

    <p>
        מספר הזמנה: <strong>#{{ $booking->serial_number }}</strong>
    </p>

    <p>
        סכום כולל: <strong>{{ $booking->total_price }} ₪</strong>
    </p>

    <br>

    <p>
        תודה שקנית אצלנו,<br>
        Wine Shop
    </p>
</div>

</body>
</html>