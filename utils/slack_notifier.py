#!/usr/bin/python

# Original Author: Moreno Zolfo (moreno.zolfo@gmail.com)
# Star Trek: Federation Slack Bot Notifier


import requests
import json
import os
import argparse
import sys
import time
from conf import DBConf
import mysql.connector

fedDb = mysql.connector.connect(
  host=DBConf.fed_host,
  user=DBConf.fed_user,
  password=DBConf.fed_passwd,
  database=DBConf.fed_database,
)

ppl = DBConf.ppl

print(fedDb)

parser=argparse.ArgumentParser()
parser.add_argument('param')
parser.add_argument('inUser')
parser.add_argument('refUser')
args = parser.parse_args()

cursor = fedDb.cursor(dictionary=True)
query = "SELECT pgUser,ordinaryUniform,pgAvatarSquare,pgGrado,pgSezione,FROM_UNIXTIME(iscriDate, '%d/%m/%Y') as iscriDate, FROM_UNIXTIME(iscriDate, '%d/%m/%Y %H:%i') as iscriDateH FROM pg_users,pg_ranks WHERE prio = rankCode AND pgID = %s LIMIT 1"
cursor.execute(query,(args.inUser,))
inUserRec = cursor.fetchone()

query = "SELECT pgUser,ordinaryUniform,pgAvatarSquare FROM pg_users,pg_ranks WHERE prio = rankCode AND pgID = %s LIMIT 1"
cursor.execute(query,(args.refUser,))
fromUserRec = cursor.fetchone()
fromHook= '<@'+ppl[fromUserRec['pgUser']]+'>' if fromUserRec['pgUser'] in ppl else fromUserRec['pgUser']
 
blocks = []
if args.param == 'approval' or args.param == 'pre-approval':
	wekbook_url ='https://hooks.slack.com/services/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'
	if args.param == 'pre-approval': 
		heading1 = ":ok: *Notifica di Pre-Approvazione BG*"
		ref1=":exploding_head: *Guida:*\n"+fromHook
	if args.param == 'approval': 
		heading1 = ":heavy_check_mark:  *Approvazione BG*"
		ref1=":briefcase: *Resp.: *\n"+fromHook

	blocks= [
		{
			"type": "section",
			"text": {
				"type": "mrkdwn",
				"text": heading1
			}
		},
		{
			"type": "section",
			"fields": [
				{
					"type": "mrkdwn",
					"text": ":bust_in_silhouette: *Utente:*\n"+inUserRec['pgUser']
				},
				{
					"type": "mrkdwn",
					"text": ref1
				},
				{
					"type": "mrkdwn",
					"text": ":starfleet-commbadge: *Grado / Sezione:*\n"+inUserRec['pgGrado']+ ' / Sez. ' +inUserRec['pgSezione']
				},
				{
					"type": "mrkdwn",
					"text": ":stopwatch: *Iscrizione:*\n"+inUserRec['iscriDate']
				}
			],
			"accessory":
				{
					"type": "image",
					"image_url": inUserRec['pgAvatarSquare'],
					"alt_text": inUserRec['pgUser']
				}
		}
	]

	data = {'blocks': blocks,'text': heading1}

if args.param == 'newuser':
	wekbook_url ='https://hooks.slack.com/services/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'
	heading1 = ":monkey: *Nuovo Utente*"

	blocks= [
		{
			"type": "section",
			"text": {
				"type": "mrkdwn",
				"text": heading1
			}
		},
		{
			"type": "section",
			"fields": [
				{
					"type": "mrkdwn",
					"text": ":bust_in_silhouette: *Utente:*\n"+inUserRec['pgUser']
				},
				{
					"type": "mrkdwn",
					"text": ":starfleet-commbadge: *Grado / Sezione:*\n"+inUserRec['pgGrado']+ ' / Sez. ' +inUserRec['pgSezione']
				},
				{
					"type": "mrkdwn",
					"text": ":stopwatch: *Iscrizione:*\n"+inUserRec['iscriDateH']
				}
			]
		}
	]

	data = {'blocks': blocks,'text': heading1}



#Hook me baybe one more time
response = requests.post(wekbook_url, data=json.dumps(data), headers={'Content-Type': 'application/json'})
