require_relative "spec_helper"

describe "A PHP application" do
	context "using ext-imap on heroku-18", :stack => "heroku-18" do
		# OpenSSL 1.1.1 introduces support for TLSv1.3
		# When negotiating a TLSv1.3 connection with a GMail IMAP server, the server will reject the attempt if no SNI ("ServerName" extension) info is sent
		# Must be fixed at the libc-client level
		it "successfully establishes a connection to a GMail IMAP server" do
			app = new_app_with_stack_and_platrepo('test/fixtures/bugs/imap-tls-sni')
			
			app.deploy do |app|
				expect(app.output).to match("- ext-imap")
				retry_until retry: 3, sleep: 5 do
					output = app.run('php -r \'imap_open("{imap.gmail.com:993/imap/ssl}INBOX", "user", "pass") or die(imap_last_error());\'')
					expect(output).to match("Can not authenticate to IMAP server")
					expect(output).not_to match("Certificate failure")
				end
			end
		end
	end
	context "that has another buildpack running after the PHP buildpack" do
		it "puts binaries from composer's bin-dir on $PATH for subsequent buildpacks" do
			buildpacks = [
				:default,
				"https://github.com/weibeld/heroku-buildpack-run"
			]
			app = new_app_with_stack_and_platrepo("test/fixtures/bugs/export-composer-bin-dir", buildpacks: buildpacks)
			app.deploy do |app|
				expect(app.output).to match("atoum version")
			end
		end
	end
end
