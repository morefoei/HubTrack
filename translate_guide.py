with open('index.php', 'r') as f:
    lines = f.readlines()

out = []
in_guide = False
for line in lines:
    if '<h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Bagian 1:' in line:
        out.append('                    <div class="lang-id">\n')
    
    out.append(line)

    if '<li><strong>Task salah masuk:</strong> Bot memprioritaskan task yang berstatus <strong>Open</strong> atau <strong>Active</strong>.</li>' in line:
        out.append('                    </ul>\n')
        out.append('                    </div>\n')
        
        # Append English Block
        en_html = """                    <div class="lang-en">
                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem; margin-top: 1rem;">Part 1: Google Sheets API Setup (Google Bot)</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Open <strong>Google Cloud Console</strong> (console.cloud.google.com).</li>
                            <li>Create a new Project (e.g., <em>TrackHub-App</em>).</li>
                            <li>Go to <strong>APIs & Services &gt; Library</strong>. Search for <strong>Google Sheets API</strong> and click <strong>Enable</strong>.</li>
                            <li>Go to <strong>APIs & Services &gt; Credentials</strong>.</li>
                            <li>Click <strong>Create Credentials &gt; Service Account</strong>. Enter a bot name (e.g., <em>zoho-bot</em>) and click Done.</li>
                            <li>Click the newly created Service Account email, go to the <strong>Keys</strong> tab, click <strong>Add Key &gt; Create New Key</strong>, and choose <strong>JSON</strong> format.</li>
                            <li>The JSON file will be downloaded. Open it, <em>Copy</em> all contents, and <em>Paste</em> it into the <strong>Google Service Account JSON</strong> field in the Settings tab.</li>
                            <li><strong>VERY IMPORTANT:</strong> <em>Copy</em> the Service Account email (e.g., <code>zoho-bot@...iam.gserviceaccount.com</code>). Open your Google Sheet, click <strong>Share</strong> in the top right, paste the email, and grant <strong>Editor</strong> access.</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 2: Zoho Projects API Setup</h3>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>Open <strong>Zoho API Console</strong> (api-console.zoho.com).</li>
                            <li>Click <strong>Add Client</strong>, then select <strong>Self Client</strong>.</li>
                            <li>Click <strong>Create</strong>. Zoho will provide a <strong>Client ID</strong> and <strong>Client Secret</strong>. <em>Copy</em> both into the Settings tab.</li>
                            <li>In the Zoho API Console, go to the <strong>Generate Code</strong> tab. Enter the following exact scope:<br>
                                <code>ZohoProjects.tasks.ALL,ZohoProjects.projects.ALL,ZohoProjects.portals.ALL,ZohoProjects.timelogs.ALL</code>
                            </li>
                            <li>Select a duration of <strong>10 Minutes</strong> or more, enter any description, and click <strong>Create</strong>. Select your portal and click <strong>Accept</strong>.</li>
                            <li>Zoho will display a temporary Authorization Code. Copy it immediately.</li>
                            <li>Open Postman or any API tool, make a <code>POST</code> request to: <br>
                                <code>https://accounts.zoho.com/oauth/v2/token?client_id=CLIENT_ID&client_secret=CLIENT_SECRET&code=AUTHORIZATION_CODE&grant_type=authorization_code</code>
                            </li>
                            <li>In the JSON response, you will get a permanent <strong>refresh_token</strong>. <em>Copy</em> this token into the <strong>Zoho Refresh Token</strong> field in Settings.</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 3: Completing App Settings</h3>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Google Spreadsheet ID:</strong> Copy the long ID from your Google Sheets URL (located between <code>/d/</code> and <code>/edit</code>).</li>
                            <li><strong>Google Sheet Tab Name:</strong> The name of the sheet tab at the bottom (e.g., <code>Sheet1</code> or <code>tasklist</code>).</li>
                            <li><strong>Zoho Portal Name:</strong> Your Zoho organization ID (e.g., <code>847721722</code>).</li>
                            <li><strong>Google Form Attendance URL (Optional):</strong> Enter the HR/HCA Google Form link here to show it in the Attendance tab.</li>
                            <li><strong>Profile Password (Required):</strong> Set a password to protect your account and tokens from other users on this server.</li>
                            <li>Click <strong>Save Settings</strong> once everything is filled out.</li>
                        </ul>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 4: Usage & Features</h3>
                        <p>After setup is complete, use the main menus to manage your work logs:</p>
                        <ol style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Daily Input:</strong> Use this to input single day logs. Click <em><i class="fa-solid fa-rotate"></i> Load Projects</em> to fetch projects directly from Zoho.</li>
                            <li><strong>Bulk Input:</strong> If you have identical logs for multiple days (e.g., Mon - Fri), select the date range, check "Exclude Weekends", and click Generate. The bot will automatically create logs in your Google Sheet!</li>
                            <li><strong>Attendance:</strong> Access this tab every morning to fill out your daily attendance. (Requires a Google Form URL in Settings).</li>
                            <li><strong>Data Logs:</strong> View all your input history here. Use the <strong>Select All</strong> checkbox to perform safe <strong>Bulk Delete</strong> or <strong>Bulk Status Updates</strong>!</li>
                            <li><strong>Sync Manager:</strong> Ensure all logs in Data Logs are marked as `final`. Open the Sync Manager tab and click <strong>Start Sync</strong> to push everything to Zoho automatically!</li>
                        </ol>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">Part 5: Forgot Password & Security</h3>
                        <p style="margin-bottom: 1rem;">This app supports a multi-user (multi-tenant) system protected by independent passwords. If you have trouble logging in:</p>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li>If you <strong>forget your profile password</strong>, you won't be able to access the dashboard or sync settings.</li>
                            <li>For privacy and security reasons, there is no automated self-recovery button on the front page.</li>
                            <li><strong>Solution:</strong> Please contact your <strong>Admin</strong> or system coordinator to request a <em>Password Reset</em>. Afterwards, you can log in and set a new password.</li>
                        </ul>

                        <h3 style="color: var(--primary); border-bottom: 1px solid var(--panel-border); padding-bottom: 0.5rem;">6. Troubleshooting</h3>
                        <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem;">
                            <li><strong>Error: <code>REMAINING_LOG_HOURS_DAYS</code>:</strong> You exceeded the maximum 24-hour log limit for a single day.</li>
                            <li><strong>Error: <code>Project not found</code>:</strong> Project name is typed incorrectly. Use <em>Load Projects</em> to ensure an exact match.</li>
                            <li><strong>Task logged incorrectly:</strong> The bot prioritizes tasks with <strong>Open</strong> or <strong>Active</strong> status.</li>
                        </ul>
                    </div>
"""
        out.append(en_html)

with open('index.php', 'w') as f:
    f.writelines(out)
