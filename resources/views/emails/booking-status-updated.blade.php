<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>עדכון סטטוס הזמנה</title>
</head>

<body style="font-family: Arial; background-color: #f5f5f5; padding: 20px; direction: rtl;">

<div style="background-color: white; padding: 20px; border-radius: 10px;">

    <h2 style="color: #7b1e3a;">עדכון סטטוס להזמנה שלך 🍷</h2>

    <p>
        שלום {{ $booking->user->name ?? 'לקוח יקר' }},
    </p>

    <p>
        רצינו לעדכן אותך שסטטוס ההזמנה שלך השתנה.
    </p>

    <p>
        הסטטוס החדש של ההזמנה הוא:
        <strong>{{ $statusHebrew }}</strong>
    </p>

    <p>
        מספר הזמנה:
        <strong>#{{ $booking->serial_number }}</strong>
    </p>

    <p>
        סכום כולל:
        <strong>{{ $booking->total_price }} ₪</strong>
    </p>

    <br>

    <p>
        תודה שקנית אצלנו,<br>
        Wine Shop
    </p>

</div>

</body>
</html>