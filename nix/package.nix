{
  lib,
  php84,
}:

php84.buildComposerProject {
  src = lib.cleanSource ./..;

  pname = "discord-events-to-ics";
  version = "0.1.3";

  php = php84.buildEnv {
    extensions = (
      { enabled, all }:
      enabled
      ++ (with all; [
        mongodb
      ])
    );
  };

  vendorHash = "sha256-wnm0sZpR8WZort1ir/H1LrC82r6piMbW0J+Uk4NQoRM=";
}
