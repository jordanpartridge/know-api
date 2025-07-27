import json
import boto3
import urllib.request
from datetime import datetime

def lambda_handler(event, context):
    # Parse SNS message
    message = json.loads(event['Records'][0]['Sns']['Message'])
    alarm_name = message.get('AlarmName', 'Unknown')
    new_state = message.get('NewStateValue', 'Unknown')
    reason = message.get('NewStateReason', 'No reason provided')
    
    # Create detailed alert
    if 'Primary' in alarm_name and new_state == 'ALARM':
        alert_msg = f"""
🚨 KNOW API ALERT 🚨
        
Primary instance (containers) is DOWN\!
• Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S UTC')}
• Alarm: {alarm_name}
• Status: {new_state}
• Reason: {reason}
• Action: Traffic automatically routed to backup instance
• Check: https://know.jordanpartridge.us/health

Next steps:
1. Check EC2 instance status
2. Check Docker containers
3. Review logs if needed
        """
    elif 'Secondary' in alarm_name and new_state == 'ALARM':
        alert_msg = f"""
⚠️ KNOW API WARNING ⚠️
        
Backup instance (gentle-garden) is DOWN\!
• Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S UTC')}
• Status: Running on primary only (no backup)
• Consider investigating backup instance
        """
    elif 'Backup-Only' in alarm_name and new_state == 'ALARM':
        alert_msg = f"""
🔄 KNOW API FAILOVER 🔄
        
API is now running on BACKUP instance\!
• Primary: DOWN
• Secondary: UP (serving traffic)
• Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S UTC')}
• URL: https://know.jordanpartridge.us still works
        """
    else:
        alert_msg = f"""
✅ KNOW API RECOVERY ✅
        
System is back to normal\!
• Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S UTC')}
• Alarm: {alarm_name}
• Status: {new_state}
        """
    
    print(alert_msg)
    return {
        'statusCode': 200,
        'body': json.dumps('Alert processed')
    }
EOF < /dev/null