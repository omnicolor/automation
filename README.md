# Home automation
Intent framework for home automation using Amazon Echo, Z-wave devices, Raspberry Pis, Kodi, and anything else I have laying around.

## Setup

1. If you don't already have an account on https://developer.amazon.com, you'll need to go create one.
2. Go to https://developer.amazon.com/edw/home.html#/skills and click the button to add a new skill.
3. **Skill Information** - Fill in information to tell Amazon about your skill and where to reach it:
   1. **Name** - Used to name your app where ever it will show up as text, like in the Alexa app or in Amazon's developer console.
   2. **Invocation Name** - What you want to call your service. My main computer's name is mordor, and I want Alexa to ask it to do something for me, so I put "mordor" here. Then I say "Alexa, ask mordor to turn on the lights" for example.
   3. **Version** - I left it blank.
   4. **Endpoint** - The URL of your server. Note that it must be behind SSL. Amazon has some decent information in the following pages for enabling SSL.
4. **Interaction Model** - Information about the intents you want to register, and what you'll say to make them happen.
  1. **Intent Schema** - JSON blob telling Alexa what "intents" you want to register, and what "slots" each has, if any. See https://raw.githubusercontent.com/omnicolor/automation/master/intent-schema.json for the intents that I've created.
  2. **Sample Utterances** - Text that tells Alexa which intents get fired when you say certain things, as well as how the slots fit. See https://raw.githubusercontent.com/omnicolor/automation/master/sample-utterances.txt for the utterances I've got.
5. **SSL Certificate** - If you don't have a real SSL certificate, you can upload the public certificate for your self-signed one here.
6. **Test** - Enable the skill for testing on this account. You can then enter utterances and click the button to test out your service.
